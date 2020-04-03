<?php


namespace BeeJee\Controller;

use BeeJee\Controller\Form\LoginForm;
use BeeJee\Service\AuthService;

class AuthController extends AbstractController
{
    public function logIn()
    {
        $form = new LoginForm($this->getCurrentPath());
        if ($form->isSubmitted() && $form->isValid()) {
            AuthService::authorizeUser($this->request->getSession());

            $this->redirect('/');
        }

        return $this->render('add-task.html.twig', ['nav' => 'auth', 'form' => $form]);
    }

    public function logOut()
    {
        AuthService::logOut($this->request->getSession());

        $this->redirect('/');
    }
}