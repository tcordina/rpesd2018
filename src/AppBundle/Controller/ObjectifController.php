<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Objectif;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;use Symfony\Component\HttpFoundation\Request;

/**
 * Objectif controller.
 *
 * @Route("objectif")
 */
class ObjectifController extends Controller
{
    /**
     * Lists all objectif entities.
     *
     * @Route("/", name="objectif_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $objectifs = $em->getRepository('AppBundle:Objectif')->findAll();

        return $this->render('objectif/index.html.twig', array(
            'objectifs' => $objectifs,
        ));
    }

    /**
     * Creates a new objectif entity.
     *
     * @Route("/new", name="objectif_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $objectif = new Objectif();
        $form = $this->createForm('AppBundle\Form\ObjectifType', $objectif);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($objectif);
            $em->flush();

            return $this->redirectToRoute('objectif_show', array('id' => $objectif->getId()));
        }

        return $this->render('objectif/new.html.twig', array(
            'objectif' => $objectif,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a objectif entity.
     *
     * @Route("/{id}", name="objectif_show")
     * @Method("GET")
     */
    public function showAction(Objectif $objectif)
    {
        $deleteForm = $this->createDeleteForm($objectif);

        return $this->render('objectif/show.html.twig', array(
            'objectif' => $objectif,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing objectif entity.
     *
     * @Route("/{id}/edit", name="objectif_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Objectif $objectif)
    {
        $deleteForm = $this->createDeleteForm($objectif);
        $editForm = $this->createForm('AppBundle\Form\ObjectifType', $objectif);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('objectif_edit', array('id' => $objectif->getId()));
        }

        return $this->render('objectif/edit.html.twig', array(
            'objectif' => $objectif,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a objectif entity.
     *
     * @Route("/{id}", name="objectif_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Objectif $objectif)
    {
        $form = $this->createDeleteForm($objectif);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($objectif);
            $em->flush();
        }

        return $this->redirectToRoute('objectif_index');
    }

    /**
     * Creates a form to delete a objectif entity.
     *
     * @param Objectif $objectif The objectif entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Objectif $objectif)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('objectif_delete', array('id' => $objectif->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
