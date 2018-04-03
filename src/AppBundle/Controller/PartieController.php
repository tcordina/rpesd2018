<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Message;
use AppBundle\Entity\Partie;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Zelenin\Elo\Match;
use Zelenin\Elo\Player;

/**
 * Partie controller.
 *
 * @Route("partie")
 */
class PartieController extends Controller
{
    /**
     * Lists all partie entities.
     *
     * @Route("/", name="partie_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $parties = $em->getRepository('AppBundle:Partie')->findAll();

        return $this->render('partie/index.html.twig', array(
            'parties' => $parties,
        ));
    }

    /**
     * Creates a new partie entity.
     *
     * @Route("/new", name="partie_new")
     * @Method("POST")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function newAction(Request $request)
    {
        $partie = new Partie();

        $form = $this->createForm('AppBundle\Form\PartieType', $partie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();
            if($partie->getJoueur2() == $user) {
                $session = new Session();
                $session->getFlashBag()->add('notice', 'Vous ne pouvez pas jouer contre vous même !');
                return $this->redirectToRoute('fos_user_profile_show');
            }
            $partie->setJoueur1($user);
            $em = $this->getDoctrine()->getManager();
            $cartes = $em->getRepository('AppBundle:Carte')->findAll();
            $objectifs = $em->getRepository('AppBundle:Objectif')->findAll();
            $actions = $em->getRepository('AppBundle:Action')->findAll();
            shuffle($cartes);
            $partie->setCarteEcartee($cartes[0]->getId());
            unset($cartes[0]);
            $cartes = array_values($cartes);

            $partie->setTourJoueurId(random_int(1,2));

            $t = array();
            for($i=0; $i<7; $i++){
                $t[] = $cartes[$i]->getId();
            }
            $partie->getTourJoueurId() == 1 ? $partie->setMainJ1(json_encode($t)) : $partie->setMainJ2(json_encode($t));

            $t = array();
            for($i=7; $i<13; $i++){
                $t[] = $cartes[$i]->getId();
            }
            $partie->getTourJoueurId() == 1 ? $partie->setMainJ2(json_encode($t)) : $partie->setMainJ1(json_encode($t));

            $t = array();
            for ($i=13; $i<count($cartes); $i++) {
                $t[] = $cartes[$i]->getId();
            }
            $partie->setPioche(json_encode($t));

            $t = array();
            foreach($objectifs as $obj) {
                $t[$obj->getId()] = [];
            }
            $partie->setTerrainJ1(json_encode($t));
            $partie->setTerrainJ2(json_encode($t));

            $t = array(
                'j1' => [],
                'j2' => [],
                'neutre' => []
            );
            foreach($objectifs as $obj){
                $t['neutre'][] = $obj->getId();
            }
            $partie->setJetons(json_encode($t));

            $t = array(
                'j1' => [],
                'j2' => []
            );
            foreach ($actions as $act){
                $t['j1'][$act->getId()-1]['id'] = $act->getId();
                $t['j1'][$act->getId()-1]['nom'] = $act->getNom();
                $t['j1'][$act->getId()-1]['jouee'] = $act->getJouee();
                $t['j1'][$act->getId()-1]['cartes'] = $act->getCartes();
                $t['j2'][$act->getId()-1]['id'] = $act->getId();
                $t['j2'][$act->getId()-1]['nom'] = $act->getNom();
                $t['j2'][$act->getId()-1]['jouee'] = $act->getJouee();
                $t['j2'][$act->getId()-1]['cartes'] = $act->getCartes();
            }
            $partie->setActions(json_encode($t));

            $t = array(
                'j1' => false,
                'j2' => false
            );
            $partie->setTourActions(json_encode($t));

            $partie->setManche(1);

            $em = $this->getDoctrine()->getManager();
            $em->persist($partie);
            $em->flush();

            $message = \Swift_Message::newInstance()
                ->setSubject('Invitation à une partie !')
                ->setFrom('noreply@rpesd2018.thibaudcordina.fr')
                ->setTo($partie->getJoueur2()->getEmail())
                ->setBody(
                    $this->renderView(
                        'mail/partie/invited.html.twig',
                        array(
                            'joueur1' => $partie->getJoueur1()->getUsername(),
                            'joueur2' => $partie->getJoueur2()->getUsername(),
                            'partie' => $partie
                        )
                    ),
                    'text/html'
                );
            $this->get('mailer')->send($message);

            return $this->redirectToRoute('partie_show', array('id' => $partie->getId()));
        }

        return $this->render('partie/new.html.twig', array(
            'partie' => $partie,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a partie entity.
     *
     * @Route("/{id}", name="partie_show")
     * @Method("GET")
     * @param Partie $partie
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showAction(Partie $partie)
    {
        if($partie->getEnded() == true) {
            return $this->render('partie/ended.html.twig', [
                'partie' => $partie
            ]);
        }

        $user = $this->get('security.token_storage')->getToken()->getUser()->getId();
        if($user == $partie->getJoueur1()->getId() || $user == $partie->getJoueur2()->getId()) {
            $deleteForm = $this->createDeleteForm($partie);
            $em = $this->getDoctrine()->getManager();
            $objectifs = $em->getRepository('AppBundle:Objectif')->findAll();
            $cartes = $em->getRepository('AppBundle:Carte')->findAll();
            $plateau = [
                'mainJ1' => json_decode($partie->getMainJ1()),
                'mainJ2' => json_decode($partie->getMainJ2()),
                'terrainJ1' => json_decode($partie->getTerrainJ1()),
                'terrainJ2' => json_decode($partie->getTerrainJ2()),
                'pioche' => json_decode($partie->getPioche()),
                'actions' => json_decode($partie->getActions()),
                'jetons' => json_decode($partie->getJetons()),
                'tourJoue' => json_decode($partie->getTourActions())
            ];
            $user == $partie->getJoueur1()->getId() ? $joueur = 1 : $joueur = 2;

            return $this->render('partie/show.html.twig', array(
                'partie' => $partie,
                'objectifs' => $objectifs,
                'cartes' => $cartes,
                'plateau' => $plateau,
                'joueur' => $joueur,
                'delete_form' => $deleteForm->createView(),
            ));
        }else {
            throw new NotFoundHttpException('Page introuvable');
        }
    }

    /**
     * Charge le plateau via appel AJAX
     * @Route("/plateau/{id}", name="partie_plateau")
     * @param Partie $partie
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function plateauAction(Partie $partie)
    {
        if($partie->getEnded() == true) {
            return $this->render('partie/ended.html.twig', [
                'partie' => $partie
            ]);
        }
        $user = $this->get('security.token_storage')->getToken()->getUser()->getId();
        if($user == $partie->getJoueur1()->getId() || $user == $partie->getJoueur2()->getId()) {
            $deleteForm = $this->createDeleteForm($partie);
            $em = $this->getDoctrine()->getManager();
            $objectifs = $em->getRepository('AppBundle:Objectif')->findAll();
            $cartes = $em->getRepository('AppBundle:Carte')->findAll();
            $plateau = [
                'mainJ1' => json_decode($partie->getMainJ1()),
                'mainJ2' => json_decode($partie->getMainJ2()),
                'terrainJ1' => json_decode($partie->getTerrainJ1(), true),
                'terrainJ2' => json_decode($partie->getTerrainJ2(), true),
                'pioche' => json_decode($partie->getPioche(), true),
                'actions' => json_decode($partie->getActions(), true),
                'jetons' => json_decode($partie->getJetons(), true),
                'tourJoue' => json_decode($partie->getTourActions(), true)
            ];
            $user == $partie->getJoueur1()->getId() ? $joueur = 1 : $joueur = 2;

            return $this->render('partie/plateau.html.twig', array(
                'partie' => $partie,
                'objectifs' => $objectifs,
                'cartes' => $cartes,
                'plateau' => $plateau,
                'joueur' => $joueur,
                'delete_form' => $deleteForm->createView(),
            ));
        }else {
            throw new NotFoundHttpException('Page introuvable');
        }
    }

    /**
     * Charge le deck via appel AJAX
     * @Route("/deck/{id}", name="partie_deck")
     * @param Partie $partie
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deckAction(Partie $partie)
    {
        if($partie->getEnded() == true) {
            return $this->render('partie/ended.html.twig', [
                'partie' => $partie
            ]);
        }
        $user = $this->get('security.token_storage')->getToken()->getUser()->getId();
        if($user == $partie->getJoueur1()->getId() || $user == $partie->getJoueur2()->getId()) {
            $deleteForm = $this->createDeleteForm($partie);
            $em = $this->getDoctrine()->getManager();
            $objectifs = $em->getRepository('AppBundle:Objectif')->findAll();
            $cartes = $em->getRepository('AppBundle:Carte')->findAll();
            $plateau = [
                'mainJ1' => json_decode($partie->getMainJ1()),
                'mainJ2' => json_decode($partie->getMainJ2()),
                'terrainJ1' => json_decode($partie->getTerrainJ1(), true),
                'terrainJ2' => json_decode($partie->getTerrainJ2(), true),
                'pioche' => json_decode($partie->getPioche(), true),
                'actions' => json_decode($partie->getActions(), true),
                'jetons' => json_decode($partie->getJetons(), true),
                'tourJoue' => json_decode($partie->getTourActions(), true)
            ];
            $user == $partie->getJoueur1()->getId() ? $joueur = 1 : $joueur = 2;

            return $this->render('partie/deck.html.twig', array(
                'partie' => $partie,
                'objectifs' => $objectifs,
                'cartes' => $cartes,
                'plateau' => $plateau,
                'joueur' => $joueur,
            ));
        }else {
            throw new NotFoundHttpException('Page introuvable');
        }
    }

    /**
     * Piocher une carte
     * @param Partie $partie
     * @param int $joueur
     * @return void
     */
    protected function piocherAction(Partie $partie, $joueur)
    {
        $pioche = json_decode($partie->getPioche());
        if(!empty($pioche)) {
            if ($joueur == 1) {
                $main = json_decode($partie->getMainJ1());
                array_push($main, $pioche[0]);
                unset($pioche[0]);
                $partie->setMainJ1(json_encode(array_values($main)));
                $partie->setPioche(json_encode(array_values($pioche)));
            } else {
                $main = json_decode($partie->getMainJ2());
                array_push($main, $pioche[0]);
                unset($pioche[0]);
                $partie->setMainJ2(json_encode(array_values($main)));
                $partie->setPioche(json_encode(array_values($pioche)));
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($partie);
            $em->flush();
        }
    }

    /**
     * Changer de tour
     * @Route("/changerTour/{partie}", name="partie_changerTour")
     * @param Partie $partie
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function changerTourAction(Partie $partie)
    {
        if($this->isFullyPlayed($partie)){
            //die(var_dump('manche finie'));
            return $this->finMancheAction($partie);
        }

        $joue = json_decode($partie->getTourActions());
        if($partie->getTourJoueurId() == 1) {
            $joue->j2 = false;
            $partie->setTourJoueurId(2);
            $this->piocherAction($partie, 2);
        }else {
            $joue->j1 = false;
            $partie->setTourJoueurId(1);
            $this->piocherAction($partie, 1);
        }
        $partie->setTourActions(json_encode($joue));

        $em = $this->getDoctrine()->getManager();
        $em->persist($partie);
        $em->flush();

        return $this->redirectToRoute('partie_show', ['id' => $partie->getId()]);
    }

    /**
     * @Route("/cetaki/{partie}", name="partie_cetaki")
     * @param Partie $partie
     * @return Response
     */
    public function cetakiAction(Partie $partie)
    {
        return new Response($partie->getTourJoueurId());
    }

    private function isFullyPlayed(Partie $partie)
    {
        $actions = json_decode($partie->getActions());
        $secretJ1 = $actions->j1[0]->jouee; $secretJ2 = $actions->j2[0]->jouee;
        $compromisJ1 = $actions->j1[1]->jouee; $compromisJ2 = $actions->j2[1]->jouee;
        $cadeauJ1 = $actions->j1[2]->jouee; $cadeauJ2 = $actions->j2[2]->jouee;
        $concurrenceJ1 = $actions->j1[3]->jouee; $concurrenceJ2 = $actions->j2[3]->jouee;
        $cartesCadeauJ1 = empty($actions->j1[2]->cartes); $cartesCadeauJ2 = empty($actions->j2[2]->cartes);
        $cartesConcurrenceJ1 = empty($actions->j1[3]->cartes); $cartesConcurrenceJ2 = empty($actions->j2[3]->cartes);
        if($secretJ1 and $secretJ2 and $compromisJ1 and $compromisJ2 and $cadeauJ1 and $cadeauJ2 and $concurrenceJ1 and $concurrenceJ2
            and $cartesCadeauJ1 and $cartesCadeauJ2 and $cartesConcurrenceJ1 and $cartesConcurrenceJ2) {
            //die(var_dump($cartesCadeauJ1, $cartesCadeauJ2));
            return true;
        }else {
            return false;
        }
    }

    /**
     * @Route("/finManche/{partie}", name="partie_fin")
     * @param Partie $partie
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function finMancheAction(Partie $partie)
    {
        $em = $this->getDoctrine()->getManager();
        $actions = json_decode($partie->getActions());
        $terrainJ1 = json_decode($partie->getTerrainJ1());
        $terrainJ2 = json_decode($partie->getTerrainJ2());
        $jetons = json_decode($partie->getJetons(), true);
        $card = $actions->j1[0]->cartes[0];
        $carte = $em->getRepository('AppBundle:Carte')->find($card);
        $objectifId = $em->getRepository('AppBundle:Objectif')->find($carte->getObjectif())->getId();
        $terrainJ1->$objectifId[] = $carte->getId();
        $actions->j1[0]->cartes = [];

        $card2 = $actions->j2[0]->cartes[0];
        $carte2 = $em->getRepository('AppBundle:Carte')->find($card2);
        $objectifId2 = $em->getRepository('AppBundle:Objectif')->find($carte2->getObjectif())->getId();
        $terrainJ2->$objectifId2[] = $carte2->getId();
        $actions->j2[0]->cartes = [];

        $partie->setTerrainJ1(json_encode($terrainJ1));
        $partie->setTerrainJ2(json_encode($terrainJ2));
        $terrainJ1 = json_decode($partie->getTerrainJ1(), true);
        $terrainJ2 = json_decode($partie->getTerrainJ2(), true);

        foreach($jetons['j1'] as $key=>$jeton){
            if(count($terrainJ1[$jeton]) < count($terrainJ2[$jeton])){
                $jetons['neutre'][] = $jeton;
                unset($jetons['j1'][$key]);
            }
        }
        $jetons['j1'] = array_values($jetons['j1']);

        foreach($jetons['j2'] as $key=>$jeton){
            if(count($terrainJ2[$jeton]) < count($terrainJ1[$jeton])){
                $jetons['neutre'][] = $jeton;
                unset($jetons['j2'][$key]);
            }
        }
        $jetons['j2'] = array_values($jetons['j2']);

        foreach($jetons['neutre'] as $key=>$jeton){
            if(count($terrainJ1[$jeton]) > count($terrainJ2[$jeton])){
                $jetons['j1'][] = $jeton;
                unset($jetons['neutre'][$key]);
            }elseif(count($terrainJ1[$jeton]) < count($terrainJ2[$jeton])){
                $jetons['j2'][] = $jeton;
                unset($jetons['neutre'][$key]);
            }
        }
        $jetons['neutre'] = array_values($jetons['neutre']);

       // die(var_dump($jetons));

        $partie->setTerrainJ1(json_encode($terrainJ1));
        $partie->setTerrainJ2(json_encode($terrainJ2));
        $partie->setJetons(json_encode($jetons));
        $partie->setActions(json_encode($actions));

        foreach ($jetons['j1'] as $jeton) {
            $valeur = $em->getRepository('AppBundle:Objectif')->find($jeton)->getValeur();
            $valeurs['j1'][] = $valeur;
        }
        foreach ($jetons['j2'] as $jeton) {
            $valeur = $em->getRepository('AppBundle:Objectif')->find($jeton)->getValeur();
            $valeurs['j2'][] = $valeur;
        }

        $em->persist($partie);
        $em->flush();

        if(array_sum($valeurs['j1']) >= 11 || count($jetons['j1']) >= 4){
            //die(var_dump('j1'));
            return $this->winAction($partie, 1);
        }elseif(array_sum($valeurs['j2']) >= 11 || count($jetons['j2']) >= 4){
            //die(var_dump('j2'));
            return $this->winAction($partie, 2);
        }else{
            //die(var_dump('nouvelle manche !!!'));
            return $this->newMancheAction($partie);
        }

        //return $this->redirectToRoute('partie_plateau', ['id' => $partie->getId()]);
    }

    public function winAction(Partie $partie, $joueur)
    {
        if($joueur == 1) {
            $partie->setWinner(1);
            $winner = $partie->getJoueur1();
            $loser = $partie->getJoueur2();
            $wElo = new Player($winner->getElo());
            $lElo = new Player($loser->getElo());
            $match = new Match($wElo, $lElo);
            $match->setScore(1,0)->setK(64)->count();
            $wNewElo = $match->getPlayer1()->getRating();
            $lNewElo = $match->getPlayer2()->getRating();
            $winner->setElo($wNewElo);
            $loser->setElo($lNewElo);
            $winner->setWins($winner->getWins()+1);
            $loser->setLosses($loser->getLosses()+1);
        }else {
            $partie->setWinner(2);
            $winner = $partie->getJoueur2();
            $loser = $partie->getJoueur1();
            $wElo = new Player($winner->getElo());
            $lElo = new Player($loser->getElo());
            $match = new Match($wElo, $lElo);
            $match->setScore(1,0)->setK(32)->count();
            $wNewElo = $match->getPlayer1()->getRating();
            $lNewElo = $match->getPlayer2()->getRating();
            $winner->setElo($wNewElo);
            $loser->setElo($lNewElo);
            $winner->setWins($winner->getWins()+1);
            $loser->setLosses($loser->getLosses()+1);
        }
        $partie->setEnded(true);
        $em = $this->getDoctrine()->getManager();
        $messages = $em->getRepository('AppBundle:Message')->findBy(['partie' => $partie->getId()]);
        $em->persist($partie);
        $em->persist($winner);
        $em->persist($loser);
        foreach($messages as $message) {
            $em->remove($message);
        }
        $em->flush();

        return $this->redirectToRoute('partie_show', [
            'id' => $partie->getId(),
        ]);
    }

    /**
     * @Route("/newManche/{partie}", name="partie_newManche")
     * @param Partie $partie
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function newMancheAction(Partie $partie)
    {
        $em = $this->getDoctrine()->getManager();
        $cartes = $em->getRepository('AppBundle:Carte')->findAll();
        $objectifs = $em->getRepository('AppBundle:Objectif')->findAll();
        $actions = $em->getRepository('AppBundle:Action')->findAll();
        shuffle($cartes);
        $partie->setCarteEcartee($cartes[0]->getId());
        unset($cartes[0]);
        $cartes = array_values($cartes);

        $partie->setTourJoueurId(random_int(1,2));

        $t = array();
        for($i=0; $i<7; $i++){
            $t[] = $cartes[$i]->getId();
        }
        $partie->getTourJoueurId() == 1 ? $partie->setMainJ1(json_encode($t)) : $partie->setMainJ2(json_encode($t));

        $t = array();
        for($i=7; $i<13; $i++){
            $t[] = $cartes[$i]->getId();
        }
        $partie->getTourJoueurId() == 1 ? $partie->setMainJ2(json_encode($t)) : $partie->setMainJ1(json_encode($t));

        $t = array();
        for ($i=13; $i<count($cartes); $i++) {
            $t[] = $cartes[$i]->getId();
        }
        $partie->setPioche(json_encode($t));

        $t = array();
        foreach($objectifs as $obj) {
            $t[$obj->getId()] = [];
        }
        $partie->setTerrainJ1(json_encode($t));
        $partie->setTerrainJ2(json_encode($t));

        $t = array(
            'j1' => [],
            'j2' => []
        );
        foreach ($actions as $act){
            $t['j1'][$act->getId()-1]['id'] = $act->getId();
            $t['j1'][$act->getId()-1]['nom'] = $act->getNom();
            $t['j1'][$act->getId()-1]['jouee'] = $act->getJouee();
            $t['j1'][$act->getId()-1]['cartes'] = $act->getCartes();
            $t['j2'][$act->getId()-1]['id'] = $act->getId();
            $t['j2'][$act->getId()-1]['nom'] = $act->getNom();
            $t['j2'][$act->getId()-1]['jouee'] = $act->getJouee();
            $t['j2'][$act->getId()-1]['cartes'] = $act->getCartes();
        }
        $partie->setActions(json_encode($t));

        $t = array(
            'j1' => false,
            'j2' => false
        );
        $partie->setTourActions(json_encode($t));

        $manche = $partie->getManche();
        $partie->setManche($manche+1);

        $em->persist($partie);
        $em->flush();

        //die(var_dump($partie));

        return $this->redirectToRoute('partie_show', array('id' => $partie->getId()));
    }

    /**
     * @Route("/message/post/{partie}", name="partie_posterMessage")
     * @param Request $request
     * @param Partie $partie
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function posterChatAction(Request $request, Partie $partie)
    {
        $em = $this->getDoctrine()->getManager();

        $content = $request->request->get('message');
        $user = $this->getUser();

        $message = new Message();
        $message->setPartie($partie->getId());
        $message->setJoueur($user->getId());
        $message->setContenu($content);

        $em->persist($message);
        $em->flush();

        return $this->redirectToRoute('partie_show', ['id' => $partie->getId()]);
    }

    /**
     * @Route("/message/{partie}", name="partie_afficherMessages")
     * @param Partie $partie
     * @return Response
     */
    public function afficherChatAction(Partie $partie)
    {
        $messages = $this->getDoctrine()->getRepository('AppBundle:Message')->findBy(
            ['partie' => $partie->getId()],
            ['id' => 'DESC'],
            10,
            0
        );

        return $this->render('partie/chat.html.twig', [
            'partie' => $partie,
            'messages' => $messages,
        ]);
    }

}
