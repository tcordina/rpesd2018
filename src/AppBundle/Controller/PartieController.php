<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Partie;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

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
     */
    public function newAction(Request $request)
    {
        $partie = new Partie();
        $user = $this->getUser();

        $form = $this->createForm('AppBundle\Form\PartieType', $partie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $partie->setJoueur1($user);
            $em = $this->getDoctrine()->getManager();
            $cartes = $em->getRepository('AppBundle:Carte')->findAll();
            $objectifs = $em->getRepository('AppBundle:Objectif')->findAll();
            $actions = $em->getRepository('AppBundle:Action')->findAll();
            shuffle($cartes);

            $t = array();
            for($i=0; $i<6; $i++){
                $t[] = $cartes[$i]->getId();
            }
            $partie->setMainJ1(json_encode($t));

            $t = array();
            for($i=6; $i<12; $i++){
                $t[] = $cartes[$i]->getId();
            }
            $partie->setMainJ2(json_encode($t));

            $t = array();
            for ($i=12; $i<count($cartes); $i++) {
                $t[] = $cartes[$i]->getId();
            }
            $partie->setPioche(json_encode($t));

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
            //die(var_dump($t, json_encode($t)));
            $partie->setActions(json_encode($t));

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
     */
    public function showAction(Partie $partie)
    {
        $deleteForm = $this->createDeleteForm($partie);
        $em = $this->getDoctrine()->getManager();
        $objectifs = $em->getRepository('AppBundle:Objectif')->findAll();
        $cartes = $em->getRepository('AppBundle:Carte')->findAll();
        $plateau = [
            'mainJ1' => json_decode($partie->getMainJ1()),
            'mainJ2' => json_decode($partie->getMainJ2()),
            'pioche' => json_decode($partie->getPioche()),
            'actions' => json_decode($partie->getActions()),
            'jetons' => json_decode($partie->getJetons())
        ];

        return $this->render('partie/show.html.twig', array(
            'partie' => $partie,
            'objectifs' => $objectifs,
            'cartes' => $cartes,
            'plateau' => $plateau,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing partie entity.
     *
     * @Route("/{id}/edit", name="partie_edit")
     * @Method({"GET", "POST"})
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
