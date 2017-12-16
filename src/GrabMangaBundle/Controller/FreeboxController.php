<?php

namespace GrabMangaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;

class FreeboxController extends Controller
{

    /**
     * @Rest\View()
     * @Rest\Post("/freebox/authorization")
     */
    public function authorizationAction(Request $request) {
        try {
            $authorization = $this->get('freebox.service')->authorize($request->getContent());
            return $authorization;
        } catch (\Exception $ex) {
            return View::create(['message' => $ex->getMessage()], $ex->getCode());
        }
    }

    /**
     * @Rest\View()
     * @Rest\Get("/freebox/tracking/{trackId}")
     */
    public function trackingAction($trackId) {
        try {
            $tracking = $this->get('freebox.service')->trackAuthorize($trackId);
            return $tracking;
        } catch (\Exception $ex) {
            return View::create(['message' => $ex->getMessage()], $ex->getCode());
        }
    }

    /**
     * @Rest\View()
     * @Rest\Get("/freebox/login")
     */
    public function loginAction() {
        try {
            $login = $this->get('freebox.service')->login();
            return $login;
        } catch (\Exception $ex) {
            return View::create(['message' => $ex->getMessage()], $ex->getCode());
        }
    }

    /**
     * @Rest\View()
     * @Rest\Post("/freebox/login/session")
     */
    public function loginSessionAction(Request $request) {
        try {
            $loginSession = $this->get('freebox.service')->loginSession($request->getContent());
            return $loginSession;
        } catch (\Exception $ex) {
            return View::create(['message' => $ex->getMessage()], $ex->getCode());
        }
    }

    /**
     * @Rest\View()
     * @Rest\Post("/freebox/downloads")
     */
    public function downloadsAction(Request $request) {
        try {
            $downloads = $this->get('freebox.service')->getDownloads($request->getContent());
            return $downloads;
        } catch (\Exception $ex) {
            return View::create(['message' => $ex->getMessage()], $ex->getCode());
        }
    }

    /**
     * @Rest\View()
     * @Rest\Post("/freebox/download")
     */
    public function downloadAction(Request $request) {
        try {
            $downloads = $this->get('freebox.service')->getDownload($request->getContent());
            return $downloads;
        } catch (\Exception $ex) {
            return View::create(['message' => $ex->getMessage()], $ex->getCode());
        }
    }

    /**
     * @Rest\View()
     * @Rest\Post("/freebox/download/delete")
     */
    public function downloadDeleteAction(Request $request) {
        try {
            $downloads = $this->get('freebox.service')->deleteDownload($request->getContent());
            return $downloads;
        } catch (\Exception $ex) {
            return View::create(['message' => $ex->getMessage()], $ex->getCode());
        }
    }

    /**
     * @Rest\View()
     * @Rest\Post("/freebox/download/status")
     */
    public function downloadStatusAction(Request $request) {
        try {
            $pause = $this->get('freebox.service')->setStatusDownload($request->getContent());
            return $pause;
        } catch (\Exception $ex) {
            return View::create(['message' => $ex->getMessage()], $ex->getCode());
        }
    }

    /**
     * @Rest\View()
     * @Rest\Post("/freebox/download/add/url")
     */
    public function downloadAddByUrlAction(Request $request) {
        try {
            $add = $this->get('freebox.service')->addDownloadByUrl($request->getContent());
            return $add;
        } catch (\Exception $ex) {
            return View::create(['message' => $ex->getMessage()], $ex->getCode());
        }
    }
}
