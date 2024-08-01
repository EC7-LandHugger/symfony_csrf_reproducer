<?php

namespace App\Controller;

use App\Enum\Direction;
use App\Service\CsrfPolluter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', 'app_homepage')]
    public function home(CsrfPolluter $csrfPolluter, Request $request): Response
    {
        if ($request->isMethod('POST')) {
            $submittedToken = $request->request->get('_csrf_token');

            $whichEndToPollute = Direction::tryFrom($request->request->get('pollutedEnd'));

            $pollutedToken = $csrfPolluter->polluteToken($submittedToken, $whichEndToPollute);

            $isPollutedTokenValid = $this->isCsrfTokenValid('planet', $pollutedToken);

            $parameters = [
                'submittedToken' => $submittedToken,
                'pollutedToken' => $pollutedToken,
                'pollutedEnd' => $whichEndToPollute->value,
                'isPollutedTokenValid' => $isPollutedTokenValid,
            ];
        }

        return $this->render('home/index.html.twig', $parameters ?? []);
    }
}
