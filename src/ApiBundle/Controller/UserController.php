<?php

namespace ApiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * User controller.
 *
 * @Route("/users")
 */
class UserController extends Controller
{
    /**
     * @Route("/", name="api_users_index")
     * @method("GET")
     * @param Request $request
     * @return Response
     */
    public function indexAction(Request $request)
    {
        header("Access-Control-Allow-Origin: *");

        $params = [];
        $criteria = [];
        $output = [];
        $em = $this->getDoctrine()->getManager();

        if(!$request->query->get('key') || !$em->getRepository('AppBundle:UserAdmin')->findOneBy(array('apiKey' => $request->query->get('key'))))
            Throw new AccessDeniedHttpException();

        if($request->query->get('id'))
            $params['id'] = (int) $request->query->get('id');
        if($request->query->get('username'))
            $params['username'] = $request->query->get('username');
        if($request->query->get('elo'))
            $params['elo'] = (int) $request->query->get('elo');
        if($request->query->get('rank'))
            $params['rank'] = $request->query->get('rank');

        if($request->query->get('orderBy')) {
            $order = $request->query->get('orderBy');
            $orderArray = explode('-', $order);
            $orderBy = array((string) $orderArray[0] => (string) $orderArray[1]);
        } else { $orderBy = null; }
        $limit = $request->query->get('limit') ? $request->query->get('limit') : null;
        $offset = $request->query->get('offset') ? $request->query->get('offset') : null;

        foreach($params as $key=>$param){
            $criteria[$key] = $param;
        }
        if(count($criteria) == 0) {
            $criteria = array('enabled' => 1);
        }

        $users = $em->getRepository('AppBundle:UserAdmin')->findBy(
            $criteria,
            $orderBy,
            $limit,
            $offset
        );

        foreach($users as $user) {
            $output[] = [
                'id' => $user->getId(),
                'username' => $user->getUsername(),
                'elo' => $user->getElo(),
                'rank' => $user->getRank(),
                'wins' => $user->getWins(),
                'losses' => $user->getLosses(),
            ];
        }

        if(isset($params['id']) || isset($params['username'])) {
            if (isset($params['id']))
                $joueur = $this->getDoctrine()->getRepository('AppBundle:UserAdmin')->find($params['id']);
            if (isset($params['username']))
                $joueur = $this->getDoctrine()->getRepository('AppBundle:UserAdmin')->findOneByUsername($params['username']);
            $partiesArray[] = $joueur->getParties1();
            $partiesArray[] = $joueur->getParties2();
            foreach ($partiesArray as $parties) {
                foreach ($parties as $partie) {
                    $output[0]['parties'][] = [
                        'id' => $partie->getId(),
                        1 => $partie->getJoueur1()->getUsername(),
                        2 => $partie->getJoueur2()->getUsername(),
                        'winner' => $partie->getWinner()
                    ];
                }
            }
            usort($output[0]['parties'], function($a, $b) {
                return $a['id'] <=> $b['id'];
            });
        }

        return new JsonResponse($output);
    }
}
