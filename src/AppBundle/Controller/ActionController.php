<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Carte;
use AppBundle\Entity\Partie;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
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
     * @Route("/secret/{partie}/{joueur}/{carte}", name="action_secret")
     * @Method("GET")
     * @param Partie $partie
     * @param $joueur
     * @param Carte $carte
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function secretAction(Partie $partie, $joueur, Carte $carte)
    {
        $em = $this->getDoctrine()->getManager();
        //$partie = $em->getRepository('AppBundle:Partie')->find($partie);

        if(in_array($carte->getId(), json_decode($partie->getMainJ1()))) {
            $actions = json_decode($partie->getActions());

            $actions->$joueur[0]->jouee = true;
            $actions->$joueur[0]->cartes = [$carte->getId()];
            $partie->setActions(json_encode($actions));

            if($joueur == 'j1'){
                $main = json_decode($partie->getMainJ1());
                if (($key = array_search($carte->getId(), $main)) !== false) {
                    unset($main[$key]);
                    $partie->setMainJ1(json_encode(array_values($main)));
                }
            }else{
                $main = json_decode($partie->getMainJ2());
                if (($key = array_search($carte->getId(), $main)) !== false) {
                    unset($main[$key]);
                    $partie->setMainJ2(json_encode(array_values($main)));
                }
            }

            $em->persist($partie);
            $em->flush();

            return $this->redirectToRoute('partie_show', ['id' => $partie->getId()]);
        }else {
            return $this->redirectToRoute('partie_show', ['id' => $partie->getId()]);
        }
    }

    /**
     * @Route("/compromis", name="action_compromis")
     * @return string
     */
    public function compromisAction()
    {
        return new Response('compromis');
    }

    /**
     * @Route("/cadeau", name="action_cadeau")
     * @return string
     */
    public function cadeauAction()
    {
        return new Response('cadeau');
    }

    /**
     * @Route("/concurrence", name="action_concurrence")
     * @return string
     */
    public function concurrenceAction()
    {
        return new Response('concurrence');
    }
}