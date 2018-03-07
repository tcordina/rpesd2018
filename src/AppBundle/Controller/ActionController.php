<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Carte;
use AppBundle\Entity\Partie;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;


/**
 * Action controller.
 *
 * @Route("action")
 */
class ActionController extends PartieController
{
    /**
     * @Route("/handle", name="action_handler")
     * @param Request $request
     * @return string|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function actionHandler(Request $request)
    {
        $partieId = (int) $request->request->get('partie');
        $partie = $this->getDoctrine()->getRepository('AppBundle:Partie')->find($partieId);
        $cards = $this->getDoctrine()->getRepository('AppBundle:Carte')->findAll();

        $action = $request->request->get('action');
        if (!empty($request->request->get('cartes'))) {
            $postCartes = $request->request->get('cartes');
            foreach($postCartes as $carte) {
                $cartes[] = $cards[$carte-1];
            }
        } else {
            $carte = $request->request->get('carte');
            $cartes = $cards[$carte-1];
        }
        $joueur = $request->request->get('joueur');
        //die(var_dump($action, $cartes, $partie, $joueur));
        switch ($action) {
            case 'secret':
                if(!isset($cartes[1])) {
                    return $this->secretAction($partie, $joueur, $cartes[0]);
                }else {
                    // return avec flashdata
                    return $this->redirectToRoute('partie_plateau', ['id' => $partie->getId()]);
                }
                break;
            case 'compromis':
                return $this->compromisAction($partie, $joueur, $cartes);
                break;
            case 'cadeau':
                return $this->cadeauAction($partie, $joueur, $cartes);
                break;
            case 'cadeau_choix':
                $objectif = $request->request->get('objectif');
                //die(var_dump($request->request));
                return $this->cadeauChoixAction($partie, $joueur, $cartes, $objectif);
                break;
            case 'concurrence':
                return $this->concurrenceAction();
                break;
        }
        return $this->redirectToRoute('partie_plateau', ['partie' => $partie]);
    }

    /**
     * @Route("/secret/{partie}/{joueur}", name="action_secret")
     * @Method("POST")
     * @param Partie $partie
     * @param $joueur
     * @param Carte $carte
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    private function secretAction(Partie $partie, $joueur, Carte $carte)
    {
        $em = $this->getDoctrine()->getManager();
        if ($joueur == 'j1') {
            if(in_array($carte->getId(), json_decode($partie->getMainJ1()))) {
                $actions = json_decode($partie->getActions());

                $actions->$joueur[0]->jouee = true;
                $actions->$joueur[0]->cartes = [$carte->getId()];
                $partie->setActions(json_encode($actions));
            }
            $main = json_decode($partie->getMainJ1());
            if (($key = array_search($carte->getId(), $main)) !== false) {
                unset($main[$key]);
                $partie->setMainJ1(json_encode(array_values($main)));
                $played = json_decode($partie->getTourActions());
                $played->j1 = true;
                $partie->setTourActions(json_encode($played));
            }
        } else {
            if(in_array($carte->getId(), json_decode($partie->getMainJ1()))) {
                $actions = json_decode($partie->getActions());

                $actions->$joueur[0]->jouee = true;
                $actions->$joueur[0]->cartes = [$carte->getId()];
                $partie->setActions(json_encode($actions));
            }
            $main = json_decode($partie->getMainJ2());
            if (($key = array_search($carte->getId(), $main)) !== false) {
                unset($main[$key]);
                $partie->setMainJ2(json_encode(array_values($main)));
                $played = json_decode($partie->getTourActions());
                $played->j2 = true;
                $partie->setTourActions(json_encode($played));
            }
        }

        $em->persist($partie);
        $em->flush();

        return $this->redirectToRoute('partie_plateau', ['id' => $partie->getId()]);
    }

    /**
     * @Route("/cadeau", name="action_cadeau")
     * @param Partie $partie
     * @param $joueur
     * @param $cartes
     * @return string
     */
    private function compromisAction(Partie $partie, $joueur, $cartes)
    {
        if($joueur == 'j1') {
            $main = json_decode($partie->getMainJ1());
            foreach ($cartes as $carte) {
                if (!in_array($carte->getId(), $main)) {
                    return $this->redirectToRoute('partie_plateau', ['partie' => $partie]);
                } else {
                    $cards[] = $carte->getId();
                    if (($key = array_search($carte->getId(), $main)) !== false) {
                        unset($main[$key]);
                    }
                }
            }
            $partie->setMainJ1(json_encode(array_values($main)));
            $played = json_decode($partie->getTourActions());
            $played->j1 = true;
            $partie->setTourActions(json_encode($played));
        }else {
            $main = json_decode($partie->getMainJ2());
            foreach ($cartes as $carte) {
                if (!in_array($carte->getId(), $main)) {
                    return $this->redirectToRoute('partie_plateau', ['partie' => $partie]);
                } else {
                    $cards[] = $carte->getId();
                    if (($key = array_search($carte->getId(), $main)) !== false) {
                        unset($main[$key]);
                    }
                }
            }
            $partie->setMainJ2(json_encode(array_values($main)));
            $played = json_decode($partie->getTourActions());
            $played->j2 = true;
            $partie->setTourActions(json_encode($played));
        }
        $actions = json_decode($partie->getActions());
        $actions->$joueur[1]->jouee = true;
        $actions->$joueur[1]->cartes = $cards;
        $partie->setActions(json_encode($actions));

        $em = $this->getDoctrine()->getManager();
        $em->persist($partie);
        $em->flush();

        return $this->redirectToRoute('partie_plateau', ['id' => $partie->getId()]);
    }

    /**
     * @Route("/compromis", name="action_compromis")
     * @param Partie $partie
     * @param $joueur
     * @param Carte $carte
     * @return string
     */
    private function cadeauAction(Partie $partie, $joueur, $cartes)
    {
        if ($joueur == 'j1') {
            $main = json_decode($partie->getMainJ1());
            foreach ($cartes as $carte) {
                if (!in_array($carte->getId(), $main)) {
                    return $this->redirectToRoute('partie_plateau', ['partie' => $partie]);
                }else {
                    $cards[] = $carte->getId();
                    if (($key = array_search($carte->getId(), $main)) !== false) {
                        unset($main[$key]);
                    }
                }
            }
            $partie->setMainJ1(json_encode(array_values($main)));
            $played = json_decode($partie->getTourActions());
            $played->j1 = true;
            $partie->setTourActions(json_encode($played));
        }else {
            $main = json_decode($partie->getMainJ2());
            foreach ($cartes as $carte) {
                if (!in_array($carte->getId(), $main)) {
                    return $this->redirectToRoute('partie_plateau', ['partie' => $partie]);
                }else {
                    $cards[] = $carte->getId();
                    if (($key = array_search($carte->getId(), $main)) !== false) {
                        unset($main[$key]);
                    }
                }
            }
            $partie->setMainJ2(json_encode(array_values($main)));
            $played = json_decode($partie->getTourActions());
            $played->j2 = true;
            $partie->setTourActions(json_encode($played));
        }
        $actions = json_decode($partie->getActions());
        $actions->$joueur[2]->jouee = true;
        $actions->$joueur[2]->cartes = $cards;
        $partie->setActions(json_encode($actions));

        $em = $this->getDoctrine()->getManager();
        $em->persist($partie);
        $em->flush();
        // creer une autre fonction qui correspond au choix du joueur adverse
        // dans vue plateau: boucle ajax qui affiche un form pour choisir carte si $action->$joueur[2]->cartes[] est non vide
        //die(var_dump($partie->getId()));
        return $this->redirectToRoute('partie_plateau', ['id' => $partie->getId()]);
    }

    private function cadeauChoixAction(Partie $partie, $joueur, $cartes, $objectifId)
    {
        //die(var_dump($objectifId));
        $actions = json_decode($partie->getActions());
        if($joueur == 'j1') {
            $terrain = json_decode($partie->getTerrainJ1());
            $carte = $cartes;
                if (!in_array($carte->getId(), $actions->$joueur[2]->cartes)) {
                    return $this->redirectToRoute('partie_plateau', ['id' => $partie->getId()]);
                }else {
                    if (($key = array_search($carte->getId(), $actions->$joueur[2]->cartes)) !== false) {
                        $terrain->$objectifId[] = $carte->getId();
                        unset($actions->$joueur[2]->cartes[$key]);
                        $actions->$joueur[2]->cartes = array_values($actions->$joueur[2]->cartes);
                        $partie->setTerrainJ1(json_encode($terrain));
                        $partie->setActions(json_encode($actions));
                    }
                }
        }else {
            $terrain = json_decode($partie->getTerrainJ2());
            $carte = $cartes;
            if (!in_array($carte->getId(), $actions->$joueur[2]->cartes)) {
                return $this->redirectToRoute('partie_plateau', ['id' => $partie->getId()]);
            }else {
                if (($key = array_search($carte->getId(), $actions->$joueur[2]->cartes)) !== false) {
                    $terrain->$objectifId[] = $carte->getId();
                    unset($actions->$joueur[2]->cartes[$key]);
                    $actions->$joueur[2]->cartes = array_values($actions->$joueur[2]->cartes);
                    $partie->setTerrainJ2(json_encode($terrain));
                    $partie->setActions(json_encode($actions));
                }
            }
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($partie);
        $em->flush();

        return $this->redirectToRoute('partie_plateau', ['id' => $partie->getId()]);
    }

    /**
     * @Route("/concurrence", name="action_concurrence")
     * @return string
     */
    private function concurrenceAction()
    {
        return new Response('concurrence');
    }
}