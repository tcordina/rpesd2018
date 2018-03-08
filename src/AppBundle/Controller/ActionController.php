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
     * @Route("/handle/{partie}/{joueur}", name="action_handler")
     * @param Request $request
     * @return string|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function actionHandler(Request $request, Partie $partie, $joueur)
    {
        $cards = $this->getDoctrine()->getRepository('AppBundle:Carte')->findAll();
        $action = $request->request->get('action');
        //die(var_dump($request->request->get('cartes')));
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
                die(var_dump($request->request));
                return $this->cadeauChoixAction($partie, $joueur, $cartes, $objectif);
                break;
            case 'concurrence':
                return $this->concurrenceAction($partie, $joueur, $cartes);
                break;
            case 'concurrence_choix':
                return $this->concurrenceChoixAction($partie, $joueur, $cartes);
                break;
            case 'concurrence_choix_2':
                $objectif = $request->request->get('objectif');
                return $this->concurrenceChoix2Action($partie, $joueur, $cartes, $objectif);
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
        if(!is_array($carte)) {
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
            } else {
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

            return $this->redirectToRoute('partie_plateau', ['id' => $partie->getId()]);
        }else {
            return $this->redirectToRoute('partie_plateau', [
                'id' => $partie->getId(),
                'flash' => 'Veuillez séléctionner une seule carte'
            ]);
        }
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
        if(is_array($cartes) && count($cartes) == 2) {
            if ($joueur == 'j1') {
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
            } else {
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
        }else {
            return $this->redirectToRoute('partie_plateau', [
                'id' => $partie->getId(),
                'flash' => 'veuillez séléctionner 2 cartes'
            ]);
        }
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
        if(is_array($cartes) && count($cartes) == 3) {
            if ($joueur == 'j1') {
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
            } else {
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
        }else {
            return $this->redirectToRoute('partie_plateau', [
                'id' => $partie->getId(),
                'flash' => 'Veuillez séléctionner 3 cartes'
            ]);
        }
    }

    private function cadeauChoixAction(Partie $partie, $joueur, $cartes, $objectifId)
    {
        die(var_dump($joueur, $cartes, $objectifId));
        if(!is_array($cartes)) {
            $actions = json_decode($partie->getActions());
            if ($joueur == 'j1') {
                $terrain = json_decode($partie->getTerrainJ1());
                $carte = $cartes;
                if (!in_array($carte->getId(), $actions->$joueur[2]->cartes)) {
                    return $this->redirectToRoute('partie_plateau', ['id' => $partie->getId()]);
                } else {
                    if (($key = array_search($carte->getId(), $actions->$joueur[2]->cartes)) !== false) {
                        $terrain->$objectifId[] = $carte->getId();
                        unset($actions->$joueur[2]->cartes[$key]);
                        $actions->$joueur[2]->cartes = array_values($actions->$joueur[2]->cartes);
                        $partie->setTerrainJ1(json_encode($terrain));
                        $partie->setActions(json_encode($actions));
                    }
                }
            } else {
                $terrain = json_decode($partie->getTerrainJ2());
                $carte = $cartes;
                if (!in_array($carte->getId(), $actions->$joueur[2]->cartes)) {
                    return $this->redirectToRoute('partie_plateau', ['id' => $partie->getId()]);
                } else {
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
        }else {
            return $this->redirectToRoute('partie_plateau', [
                'id' => $partie->getId(),
                'flash' => 'Veuillez séléctionner une carte'
            ]);
        }
    }

    /**
     * @Route("/concurrence", name="action_concurrence")
     * @return string
     */
    private function concurrenceAction(Partie $partie, $joueur, $cartes)
    {
        if(is_array($cartes) && count($cartes) == 4) {
            if ($joueur == 'j1') {
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
            } else {
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
            $actions->$joueur[3]->jouee = true;
            $actions->$joueur[3]->cartes = $cards;
            $partie->setActions(json_encode($actions));

            $em = $this->getDoctrine()->getManager();
            $em->persist($partie);
            $em->flush();

            return $this->redirectToRoute('partie_plateau', ['id' => $partie->getId()]);
        }else {
            return $this->redirectToRoute('partie_plateau', [
                'id' => $partie->getId(),
                'flash' => 'Veuillez séléctionner 4 cartes'
            ]);
        }
    }

    private function concurrenceChoixAction(Partie $partie, $joueur, $cartes)
    {
        if(is_array($cartes) && count($cartes) == 2) {
            $actions = json_decode($partie->getActions());
            $actions->$joueur[3]->choisies = [
                'j1' => [],
                'j2' => []
            ];
            if ($joueur == 'j1') {
                foreach($cartes as $carte) {
                    if (!in_array($carte->getId(), $actions->$joueur[3]->cartes)) {
                        return $this->redirectToRoute('partie_plateau', ['id' => $partie->getId()]);
                    } else {
                        if (($key = array_search($carte->getId(), $actions->$joueur[3]->cartes)) !== false) {
                            $actions->$joueur[3]->choisies['j1'][] = $actions->$joueur[3]->cartes[$key];
                            unset($actions->$joueur[3]->cartes[$key]);
                        }
                    }
                }
                $actions->$joueur[3]->cartes = array_values($actions->$joueur[3]->cartes);
                $actions->$joueur[3]->choisies['j2'] = $actions->$joueur[3]->cartes;
                unset($actions->$joueur[3]->cartes[0]);
                unset($actions->$joueur[3]->cartes[1]);
                //die(var_dump($actions->$joueur[3]));
            } else {
                foreach($cartes as $carte) {
                    if (!in_array($carte->getId(), $actions->$joueur[3]->cartes)) {
                        return $this->redirectToRoute('partie_plateau', ['id' => $partie->getId()]);
                    } else {
                        if (($key = array_search($carte->getId(), $actions->$joueur[3]->cartes)) !== false) {
                            $actions->$joueur[3]->choisies['j2'][] = $actions->$joueur[3]->cartes[$key];
                            unset($actions->$joueur[3]->cartes[$key]);
                        }
                    }
                }
                $actions->$joueur[3]->cartes = array_values($actions->$joueur[3]->cartes);
                $actions->$joueur[3]->choisies['j2'] = $actions->$joueur[3]->cartes;
                unset($actions->$joueur[3]->cartes[0]);
                unset($actions->$joueur[3]->cartes[1]);
                //die(var_dump($actions->$joueur[3]));
            }
            $partie->setActions(json_encode($actions));

            $em = $this->getDoctrine()->getManager();
            $em->persist($partie);
            $em->flush();

            return $this->redirectToRoute('partie_plateau', ['id' => $partie->getId()]);
        }else {
            return $this->redirectToRoute('partie_plateau', [
                'id' => $partie->getId(),
                'flash' => 'Veuillez séléctionner 2 cartes'
            ]);
        }
    }

    private function concurrenceChoix2Action(Partie $partie, $joueur, $carte, $objectifId)
    {
        if(!is_array($carte)) {
            $actions = json_decode($partie->getActions());
            if ($joueur == 'j1') {
                $terrain = json_decode($partie->getTerrainJ1());
                //die(var_dump($actions->$joueur[3]->choisies->$joueur));
                if (!in_array($carte->getId(), $actions->$joueur[3]->choisies->$joueur)) {
                    return $this->redirectToRoute('partie_plateau', ['id' => $partie->getId()]);
                } else {
                    if (($key = array_search($carte->getId(), $actions->$joueur[3]->choisies->$joueur)) !== false) {
                        $terrain->$objectifId[] = $carte->getId();
                        unset($actions->$joueur[3]->choisies->$joueur[$key]);
                        $actions->$joueur[3]->choisies->$joueur = array_values($actions->$joueur[3]->choisies->$joueur);
                        $partie->setTerrainJ1(json_encode($terrain));
                        $partie->setActions(json_encode($actions));
                    }
                }
            } else {
                $terrain = json_decode($partie->getTerrainJ2());
                if (!in_array($carte->getId(), $actions->$joueur[3]->choisies->$joueur)) {
                    return $this->redirectToRoute('partie_plateau', ['id' => $partie->getId()]);
                } else {
                    if (($key = array_search($carte->getId(), $actions->$joueur[3]->choisies->$joueur)) !== false) {
                        $terrain->$objectifId[] = $carte->getId();
                        unset($actions->$joueur[3]->choisies->$joueur[$key]);
                        $actions->$joueur[3]->choisies->$joueur = array_values($actions->$joueur[3]->choisies->$joueur);
                        $partie->setTerrainJ2(json_encode($terrain));
                        $partie->setActions(json_encode($actions));
                    }
                }
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($partie);
            $em->flush();

            return $this->redirectToRoute('partie_plateau', ['id' => $partie->getId()]);
        }else {
            return $this->redirectToRoute('partie_plateau', [
                'id' => $partie->getId(),
                'flash' => 'Veuillez séléctionner une carte'
            ]);
        }
    }
}