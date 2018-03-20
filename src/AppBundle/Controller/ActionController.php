<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Carte;
use AppBundle\Entity\Partie;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;


/**
 * Action controller.
 *
 * @Route("action")
 */
class ActionController extends PartieController
{
    /**
     * @Route("/handle/{partie}/{joueur}", name="action_handler")
     * @param Request $request
     * @param Partie $partie
     * @param $joueur
     * @return string|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function actionHandler(Request $request, Partie $partie, $joueur)
    {
        $em = $this->getDoctrine()->getManager();
        $cards = $em->getRepository('AppBundle:Carte')->findAll();
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

        switch ($action) {
            case 'secret':
                return $this->secretAction($partie, $joueur, $cartes);
                break;
            case 'compromis':
                return $this->compromisAction($partie, $joueur, $cartes);
                break;
            case 'cadeau':
                return $this->cadeauAction($partie, $joueur, $cartes);
                break;
            case 'cadeau_choix':
                return $this->cadeauChoixAction($partie, $joueur, $cartes);
                break;
            case 'concurrence':
                return $this->concurrenceAction($partie, $joueur, $cartes);
                break;
            case 'concurrence_choix':
                return $this->concurrenceChoixAction($partie, $joueur, $cartes);
                break;
        }
        return $this->redirectToRoute('partie_show', ['id' => $partie->getId()]);
    }

    private function secretAction(Partie $partie, $joueur, $carte)
    {
        if(!isset($carte[1])) {
            $carte = $carte[0];
            if ($joueur == 'j1') {
                $main = json_decode($partie->getMainJ1());
                if (in_array($carte->getId(), $main)) {
                    $actions = json_decode($partie->getActions());
                    $actions->$joueur[0]->jouee = true;
                    $actions->$joueur[0]->cartes = [$carte->getId()];
                    $partie->setActions(json_encode($actions));
                }
                if (($key = array_search($carte->getId(), $main)) !== false) {
                    unset($main[$key]);
                    $partie->setMainJ1(json_encode(array_values($main)));
                    $played = json_decode($partie->getTourActions());
                    $played->j1 = true;
                    $partie->setTourActions(json_encode($played));
                }
            } elseif ($joueur == 'j2') {
                $main = json_decode($partie->getMainJ2());
                if (in_array($carte->getId(), $main)) {
                    $actions = json_decode($partie->getActions());
                    $actions->$joueur[0]->jouee = true;
                    $actions->$joueur[0]->cartes = [$carte->getId()];
                    $partie->setActions(json_encode($actions));
                }
                if (($key = array_search($carte->getId(), $main)) !== false) {
                    unset($main[$key]);
                    $partie->setMainJ2(json_encode(array_values($main)));
                    $played = json_decode($partie->getTourActions());
                    $played->j2 = true;
                    $partie->setTourActions(json_encode($played));
                }
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($partie);
            $em->flush();

            $this->changerTourAction($partie);

            return $this->redirectToRoute('partie_show', ['id' => $partie->getId()]);
        }else {
            $session = new Session();
            $session->getFlashBag()->add('notice', 'Veuillez séléctionner une seule carte');
            return $this->redirectToRoute('partie_show', [
                'id' => $partie->getId(),
            ]);
        }
    }

    private function compromisAction(Partie $partie, $joueur, $cartes)
    {
        if(is_array($cartes) && count($cartes) == 2) {
            if ($joueur == 'j1') {
                $main = json_decode($partie->getMainJ1());
                foreach ($cartes as $carte) {
                    if (!in_array($carte->getId(), $main)) {
                        return $this->redirectToRoute('partie_show', ['id' => $partie->getId()]);
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
            } elseif ($joueur == 'j2') {
                $main = json_decode($partie->getMainJ2());
                foreach ($cartes as $carte) {
                    if (!in_array($carte->getId(), $main)) {
                        return $this->redirectToRoute('partie_show', ['id' => $partie->getId()]);
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

            $this->changerTourAction($partie);

            return $this->redirectToRoute('partie_show', ['id' => $partie->getId()]);
        }else {
            $session = new Session();
            $session->getFlashBag()->add('notice', 'Veuillez séléctionner 2 cartes');
            return $this->redirectToRoute('partie_show', [
                'id' => $partie->getId(),
            ]);
        }
    }

    private function cadeauAction(Partie $partie, $joueur, $cartes)
    {
        if(is_array($cartes) && count($cartes) == 3) {
            if ($joueur == 'j1') {
                $main = json_decode($partie->getMainJ1());
                foreach ($cartes as $carte) {
                    if (!in_array($carte->getId(), $main)) {
                        return $this->redirectToRoute('partie_show', ['id' => $partie->getId()]);
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
            } elseif ($joueur == 'j2') {
                $main = json_decode($partie->getMainJ2());
                foreach ($cartes as $carte) {
                    if (!in_array($carte->getId(), $main)) {
                        return $this->redirectToRoute('partie_show', ['id' => $partie->getId()]);
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
            $actions->$joueur[2]->cartes = $cards;
            $partie->setActions(json_encode($actions));

            //die(var_dump(json_decode($partie->getActions())->j1[2], json_decode($partie->getActions())->j2[2]));
            $em = $this->getDoctrine()->getManager();
            $em->persist($partie);
            $em->flush();

            $this->changerTourAction($partie);

            return $this->redirectToRoute('partie_show', ['id' => $partie->getId()]);
        }else {
            $session = new Session();
            $session->getFlashBag()->add('notice', 'Veuillez séléctionner 3 cartes');
            return $this->redirectToRoute('partie_show', [
                'id' => $partie->getId(),
            ]);
        }
    }

    private function cadeauChoixAction(Partie $partie, $joueur, Carte $carte)
    {
        $em = $this->getDoctrine()->getManager();
        $objectif = $em->getRepository('AppBundle:Objectif')->find($carte->getObjectif());
        $objectifId = $objectif->getId();
        if (!is_array($carte)) {
            $actions = json_decode($partie->getActions());
            if ($joueur == 'j1') {
                $terrainJ1 = json_decode($partie->getTerrainJ1());
                if (!in_array($carte->getId(), $actions->j2[2]->cartes)) {
                    return $this->redirectToRoute('partie_show', ['id' => $partie->getId()]);
                } else {
                    if (($key = array_search($carte->getId(), $actions->j2[2]->cartes)) !== false) {
                        $terrainJ1->$objectifId[] = $carte->getId();
                        unset($actions->j2[2]->cartes[$key]);
                        $actions->j2[2]->cartes = array_values($actions->j2[2]->cartes);
                        $partie->setTerrainJ1(json_encode($terrainJ1));

                        $terrainJ2 = json_decode($partie->getTerrainJ2());
                        foreach($actions->j2[2]->cartes as $card) {
                            $carte = $em->getRepository('AppBundle:Carte')->find($card);
                            $objectifId = $em->getRepository('AppBundle:Objectif')->find($carte->getObjectif())->getId();
                            $terrainJ2->$objectifId[] = $carte->getId();
                        }
                        $actions->j2[2]->cartes = [];
                        $actions->j2[2]->jouee = true;
                        $partie->setTerrainJ2(json_encode($terrainJ2));
                        $partie->setActions(json_encode($actions));

                    }
                }
            } elseif ($joueur == 'j2') {
                $terrainJ2 = json_decode($partie->getTerrainJ2());
                if (!in_array($carte->getId(), $actions->j1[2]->cartes)) {
                    return $this->redirectToRoute('partie_show', ['id' => $partie->getId()]);
                } else {
                    if (($key = array_search($carte->getId(), $actions->j1[2]->cartes)) !== false) {
                        $terrainJ2->$objectifId[] = $carte->getId();
                        unset($actions->j1[2]->cartes[$key]);
                        $actions->j1[2]->cartes = array_values($actions->j1[2]->cartes);
                        $partie->setTerrainJ2(json_encode($terrainJ2));

                        $terrainJ1 = json_decode($partie->getTerrainJ1());
                        foreach($actions->j1[2]->cartes as $card) {
                            $carte = $em->getRepository('AppBundle:Carte')->find($card);
                            $objectifId = $em->getRepository('AppBundle:Objectif')->find($carte->getObjectif())->getId();
                            $terrainJ1->$objectifId[] = $carte->getId();
                        }
                        $actions->j1[2]->cartes = [];
                        $actions->j1[2]->jouee = true;
                        $partie->setTerrainJ1(json_encode($terrainJ1));
                        $partie->setActions(json_encode($actions));
                    }
                }
            }

            $em->persist($partie);
            $em->flush();

            if(empty(json_decode($partie->getMainJ1())) && empty(json_decode($partie->getMainJ2()))) {
                return $this->changerTourAction($partie);
            }

            return $this->redirectToRoute('partie_show', ['id' => $partie->getId()]);
        } else {
            $session = new Session();
            $session->getFlashBag()->add('notice', 'Veuillez séléctionner une carte');
            return $this->redirectToRoute('partie_show', [
                'id' => $partie->getId(),
            ]);
        }
    }

    private function concurrenceAction(Partie $partie, $joueur, $cartes)
    {
        if(is_array($cartes) && count($cartes) == 4) {
            if ($joueur == 'j1') {
                $main = json_decode($partie->getMainJ1());
                foreach ($cartes as $carte) {
                    if (!in_array($carte->getId(), $main)) {
                        return $this->redirectToRoute('partie_show', ['id' => $partie->getId()]);
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
            } elseif ($joueur == 'j2') {
                $main = json_decode($partie->getMainJ2());
                foreach ($cartes as $carte) {
                    if (!in_array($carte->getId(), $main)) {
                        return $this->redirectToRoute('partie_show', ['id' => $partie->getId()]);
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
            //die(var_dump($cards));
            $actions = json_decode($partie->getActions());
            $actions->$joueur[3]->cartes = $cards;
            $partie->setActions(json_encode($actions));

            $em = $this->getDoctrine()->getManager();
            $em->persist($partie);
            $em->flush();

            $this->changerTourAction($partie);

            return $this->redirectToRoute('partie_show', ['id' => $partie->getId()]);
        }else {
            $session = new Session();
            $session->getFlashBag()->add('notice', 'Veuillez séléctionner 4 cartes');
            return $this->redirectToRoute('partie_show', [
                'id' => $partie->getId(),
            ]);
        }
    }

    private function concurrenceChoixAction(Partie $partie, $joueur, $cartes)
    {
        //die(var_dump($cartes));
        $em = $this->getDoctrine()->getManager();
        if(is_array($cartes) && count($cartes) == 2) {
            $actions = json_decode($partie->getActions());
            if ($joueur == 'j1') {
                foreach($cartes as $carte) {
                    if (!in_array($carte->getId(), $actions->j2[3]->cartes)) {
                        return $this->redirectToRoute('partie_show', ['id' => $partie->getId()]);
                    } else {
                        if (($key = array_search($carte->getId(), $actions->j2[3]->cartes)) !== false) {
                            $actions->j2[3]->choisies['j1'][] = $actions->j2[3]->cartes[$key];
                            unset($actions->j2[3]->cartes[$key]);
                        }
                    }
                }
                $actions->j2[3]->cartes = array_values($actions->j2[3]->cartes);
                $actions->j2[3]->choisies['j2'] = $actions->j2[3]->cartes;
                unset($actions->j2[3]->cartes[0]);
                unset($actions->j2[3]->cartes[1]);
                //die(var_dump($actions->j2[3]->cartes, $actions->j2[3]->choisies));

                $terrainJ1 = json_decode($partie->getTerrainJ1());
                $terrainJ2 = json_decode($partie->getTerrainJ2());
                foreach($actions->j2[3]->choisies['j2'] as $card) {
                    $carte = $em->getRepository('AppBundle:Carte')->find($card);
                    $objectifId = $em->getRepository('AppBundle:Objectif')->find($carte->getObjectif())->getId();
                    $terrainJ2->$objectifId[] = $carte->getId();
                }
                $actions->j2[3]->choisies['j2'] = [];
                foreach($actions->j2[3]->choisies['j1'] as $card) {
                    $carte = $em->getRepository('AppBundle:Carte')->find($card);
                    $objectifId = $em->getRepository('AppBundle:Objectif')->find($carte->getObjectif())->getId();
                    $terrainJ1->$objectifId[] = $carte->getId();
                }
                $actions->j2[3]->choisies['j1'] = [];
                $actions->j2[3]->jouee = true;
                $partie->setTerrainJ1(json_encode($terrainJ1));
                $partie->setTerrainJ2(json_encode($terrainJ2));
                //die(var_dump($actions));
            } elseif ($joueur == 'j2') {
                foreach($cartes as $carte) {
                    if (!in_array($carte->getId(), $actions->j1[3]->cartes)) {
                        return $this->redirectToRoute('partie_show', ['id' => $partie->getId()]);
                    } else {
                        if (($key = array_search($carte->getId(), $actions->j1[3]->cartes)) !== false) {
                            $actions->j1[3]->choisies['j2'][] = $actions->j1[3]->cartes[$key];
                            unset($actions->j1[3]->cartes[$key]);
                        }
                    }
                }
                $actions->j1[3]->cartes = array_values($actions->j1[3]->cartes);
                $actions->j1[3]->choisies['j1'] = $actions->j1[3]->cartes;
                unset($actions->j1[3]->cartes[0]);
                unset($actions->j1[3]->cartes[1]);

                $terrainJ1 = json_decode($partie->getTerrainJ1());
                $terrainJ2 = json_decode($partie->getTerrainJ2());
                foreach($actions->j1[3]->choisies['j2'] as $card) {
                    $carte = $em->getRepository('AppBundle:Carte')->find($card);
                    $objectifId = $em->getRepository('AppBundle:Objectif')->find($carte->getObjectif())->getId();
                    $terrainJ2->$objectifId[] = $carte->getId();
                }
                $actions->j1[3]->choisies['j2'] = [];
                foreach($actions->j1[3]->choisies['j1'] as $card) {
                    $carte = $em->getRepository('AppBundle:Carte')->find($card);
                    $objectifId = $em->getRepository('AppBundle:Objectif')->find($carte->getObjectif())->getId();
                    $terrainJ1->$objectifId[] = $carte->getId();
                }
                $actions->j1[3]->choisies['j1'] = [];
                $actions->j1[3]->jouee = true;
                $partie->setTerrainJ1(json_encode($terrainJ1));
                $partie->setTerrainJ2(json_encode($terrainJ2));
            }
            $partie->setActions(json_encode($actions));

            $em->persist($partie);
            $em->flush();

            if(empty(json_decode($partie->getMainJ1())) && empty(json_decode($partie->getMainJ2()))) {
                return $this->changerTourAction($partie);
            }

            return $this->redirectToRoute('partie_show', ['id' => $partie->getId()]);
        }else {
            $session = new Session();
            $session->getFlashBag()->add('notice', 'Veuillez séléctionner 2 cartes');
            return $this->redirectToRoute('partie_show', [
                'id' => $partie->getId(),
            ]);
        }
    }
}