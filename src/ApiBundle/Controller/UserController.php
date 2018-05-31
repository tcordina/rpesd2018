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
        $criteria = ['enabled' => 1];
        $output = [];
        $em = $this->getDoctrine()->getManager();

        $caller = $em->getRepository('AppBundle:UserAdmin')->findOneBy(['apiKey' => $request->query->get('key')]);

        if(!$request->query->get('key') || !$caller)
            Throw new AccessDeniedHttpException();

        $calls = $caller->getApiCalls() == null ? 0 : $caller->getApiCalls();
        $caller->setApiCalls($calls + 1);

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
        $showParties = $request->query->get('parties') ? filter_var($request->query->get('parties'), FILTER_VALIDATE_BOOLEAN) : 0;
        $showEnCours = $request->query->get('playing') ? filter_var($request->query->get('playing'), FILTER_VALIDATE_BOOLEAN) : 0;

        foreach($params as $key=>$param){
            $criteria[$key] = $param;
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

        if((isset($params['id']) || isset($params['username'])) && $showParties == true) {
            if (isset($params['id']))
                $joueur = $this->getDoctrine()->getRepository('AppBundle:UserAdmin')->find($params['id']);
            if (isset($params['username']))
                $joueur = $this->getDoctrine()->getRepository('AppBundle:UserAdmin')->findOneByUsername($params['username']);
            $partiesArray[] = $joueur->getParties1();
            $partiesArray[] = $joueur->getParties2();
            foreach ($partiesArray as $parties) {
                foreach ($parties as $partie) {
                    if($showEnCours == false && $partie->getEnded() == 0) {
                        continue;
                    }
                    if($partie->getWinner() == 1) {
                        $winner = 'player1';
                    }elseif($partie->getWinner() == 2) {
                        $winner = 'player2';
                    }else {
                        $winner = null;
                    }
                    $output[0]['parties'][] = [
                        'id' => $partie->getId(),
                        'player1' => [
                            'id' => $partie->getJoueur1()->getId(),
                            'username' => $partie->getJoueur1()->getUsername(),
                            'elo' => $partie->getJoueur1()->getElo(),
                            'rank' => $partie->getJoueur1()->getRank(),
                            'wins' => $partie->getJoueur1()->getWins(),
                            'losses' => $partie->getJoueur1()->getLosses(),
                        ],
                        'player2' => [
                            'id' => $partie->getJoueur2()->getId(),
                            'username' => $partie->getJoueur2()->getUsername(),
                            'elo' => $partie->getJoueur2()->getElo(),
                            'rank' => $partie->getJoueur2()->getRank(),
                            'wins' => $partie->getJoueur2()->getWins(),
                            'losses' => $partie->getJoueur2()->getLosses(),
                        ],
                        'winner' => $winner,
                        'rounds' => $partie->getManche(),
                        'created_at' => $partie->getCreatedAt(),
                    ];
                }
            }
            usort($output[0]['parties'], function($a, $b) {
                return $a['id'] <=> $b['id'];
            });
        }

        $em->persist($caller);
        $em->flush();

        return new JsonResponse($output);
    }
}
