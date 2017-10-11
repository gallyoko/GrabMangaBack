<?php

namespace GrabMangaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;

class SecurityController extends Controller
{
    /**
     * @Rest\View()
     * @Rest\Post("/auth")
     */
    public function authAction(Request $request) {
        try {
            return $this->get('security.service')->auth($request->getContent());
        } catch (\Exception $ex) {
            return View::create(['message' => $ex->getMessage()], $ex->getCode());
        }
    }

}
