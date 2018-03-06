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
        $action = $request->request->get('action');
        $postCartes = $request->request->get('cartes');
        $partieId = (int) $request->request->get('partie');
        $joueur = $request->request->get('joueur');
        $partie = $this->getDoctrine()->getRepository('AppBundle:Partie')->find($partieId);
        $cards = $this->getDoctrine()->getRepository('AppBundle:Carte')->findAll();
        foreach($postCartes as $carte) {
            $cartes[] = $cards[$carte-1];
        }
        //die(var_dump($action, $cartes, $partie, $joueur));
        switch ($action) {
            case 'secret':
                if(!isset($cartes[1])) {
                    return $this->secretAction($partie, $joueur, $cartes[0]);
                }else {
                    // return avec flashdata
                    return $this->redirectToRoute('partie_plateau', ['partie' => $partie]);
                }
                break;
            case 'compromis':
                return $this->compromisAction();
                break;
            case 'cadeau':
                return $this->cadeauAction();
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
        if(in_array($carte->getId(), json_decode($partie->getMainJ1()))) {
            $actions = json_decode($partie->getActions());

            $actions->$joueur[0]->jouee = true;
            $actions->$joueur[0]->cartes = [$carte->getId()];
            $partie->setActions(json_encode($actions));

            if ($joueur == 'j1') {
                $main = json_decode($partie->getMainJ1());
                if (($key = array_search($carte->getId(), $main)) !== false) {
                    unset($main[$key]);
                    $partie->setMainJ1(json_encode(array_values($main)));
                    $played = json_decode($partie->getTourActions());
                    $played->j1 = true;
                    $partie->setTourActions(json_encode($played));
                }
            } else {
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
        }else {
            // return avec un flashdata
            return $this->redirectToRoute('partie_plateau', ['id' => $partie->getId()]);
        }
    }

    /**
     * @Route("/compromis", name="action_compromis")
     * @return string
     */
    private function compromisAction()
    {
        return new Response('compromis');
    }

    /**
     * @Route("/cadeau", name="action_cadeau")
     * @return string
     */
    private function cadeauAction()
    {
        return new Response('cadeau');
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