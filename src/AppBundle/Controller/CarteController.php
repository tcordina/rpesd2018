<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Carte;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;use Symfony\Component\HttpFoundation\Request;

/**
 * Carte controller.
 *
 * @Route("carte")
 */
class CarteController extends Controller
{
    /**
     * Lists all carte entities.
     *
     * @Route("/", name="carte_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $cartes = $em->getRepository('AppBundle:Carte')->findAll();

        return $this->render('carte/index.html.twig', array(
            'cartes' => $cartes,
        ));
    }

    /**
     * Creates a new carte entity.
     *
     * @Route("/new", name="carte_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $carte = new Carte();
        $form = $this->createForm('AppBundle\Form\CarteType', $carte);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($carte);
            $em->flush();

            return $this->redirectToRoute('carte_show', array('id' => $carte->getId()));
        }

        return $this->render('carte/new.html.twig', array(
            'carte' => $carte,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a carte entity.
     *
     * @Route("/{id}", name="carte_show")
     * @Method("GET")
     */
    public function showAction(Carte $carte)
    {
        $deleteForm = $this->createDeleteForm($carte);

        return $this->render('carte/show.html.twig', array(
            'carte' => $carte,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing carte entity.
     *
     * @Route("/{id}/edit", name="carte_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Carte $carte)
    {
        $deleteForm = $this->createDeleteForm($carte);
        $editForm = $this->createForm('AppBundle\Form\CarteType', $carte);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('carte_edit', array('id' => $carte->getId()));
        }

        return $this->render('carte/edit.html.twig', array(
            'carte' => $carte,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a carte entity.
     *
     * @Route("/{id}", name="carte_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Carte $carte)
    {
        $form = $this->createDeleteForm($carte);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($carte);
            $em->flush();
        }

        return $this->redirectToRoute('carte_index');
    }

    /**
     * Creates a form to delete a carte entity.
     *
     * @param Carte $carte The carte entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Carte $carte)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('carte_delete', array('id' => $carte->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
