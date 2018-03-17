<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Partie;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
     * @Method({"GET", "POST"})
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function newAction(Request $request)
    {
        $partie = new Partie();

        $form = $this->createForm('AppBundle\Form\PartieType', $partie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->get('security.token_storage')->getToken()->getUser();
            $partie->setJoueur1($user);
            $em = $this->getDoctrine()->getManager();
            $cartes = $em->getRepository('AppBundle:Carte')->findAll();
            $objectifs = $em->getRepository('AppBundle:Objectif')->findAll();
            $actions = $em->getRepository('AppBundle:Action')->findAll();
            shuffle($cartes);
            $partie->setCarteEcartee($cartes[0]->getId());
            unset($cartes[0]);
            $cartes = array_values($cartes);

            $t = array();
            for($i=0; $i<7; $i++){
                $t[] = $cartes[$i]->getId();
            }
            $partie->setMainJ1(json_encode($t));

            $t = array();
            for($i=7; $i<13; $i++){
                $t[] = $cartes[$i]->getId();
            }
            $partie->setMainJ2(json_encode($t));

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
                'j2' => true
            );
            $partie->setTourActions(json_encode($t));

            $em = $this->getDoctrine()->getManager();
            $em->persist($partie);
            $em->flush();

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
     * Piocher une carte
     * @param Partie $partie
     * @param int $joueur
     * @return void
     */
    protected function piocherAction(Partie $partie, $joueur)
    {
        $pioche = json_decode($partie->getPioche());
        if(empty($pioche)) {
            return $this->finMancheAction($partie);
        }
        if($joueur == 1) {
            $main = json_decode($partie->getMainJ1());
            array_push($main, $pioche[0]);
            unset($pioche[0]);
            $partie->setMainJ1(json_encode(array_values($main)));
            $partie->setPioche(json_encode(array_values($pioche)));
        }else {
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

    /**
     * Changer de tour
     * @Route("/changerTour/{partie}", name="partie_changerTour")
     * @Method("POST")
     * @param Partie $partie
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function changerTourAction(Partie $partie)
    {
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

    public function finMancheAction(Partie $partie)
    {
        return $this->redirectToRoute('partie_plateau', ['id' => $partie->getId()]);
    }

    /**
     * Displays a form to edit an existing partie entity.
     *
     * @Route("/{id}/edit", name="partie_edit")
     * @Method({"GET", "POST"})
     * @param Request $request
     * @param Partie $partie
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Request $request, Partie $partie)
    {
        $deleteForm = $this->createDeleteForm($partie);
        $editForm = $this->createForm('AppBundle\Form\PartieType', $partie);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('partie_edit', array('id' => $partie->getId()));
        }

        return $this->render('partie/edit.html.twig', array(
            'partie' => $partie,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a partie entity.
     *
     * @Route("/{id}", name="partie_delete")
     * @Method("DELETE")
     * @param Request $request
     * @param Partie $partie
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction(Request $request, Partie $partie)
    {
        $form = $this->createDeleteForm($partie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($partie);
            $em->flush();
        }

        return $this->redirectToRoute('partie_index');
    }

    /**
     * Creates a form to delete a partie entity.
     *
     * @param Partie $partie The partie entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Partie $partie)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('partie_delete', array('id' => $partie->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
