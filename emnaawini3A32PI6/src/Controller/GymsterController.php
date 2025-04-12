<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GymsterController extends AbstractController
{
    #[Route('/home', name: 'app_home1')]
    public function home(): Response
    {
        return $this->render('gym-html-template/home.html.twig');
    }

    #[Route('/about', name: 'app_about1')]
    public function about(): Response
    {
        return $this->render('gym-html-template/about.html.twig');
    }

    #[Route('/blog', name: 'app_blog1')]
    public function blog(): Response
    {
        return $this->render('gym-html-template/blog.html.twig');
    }

    #[Route('/detail', name: 'app_detail1')]
    public function detail(): Response
    {
        return $this->render('gym-html-template/detail.html.twig');
    }

    #[Route('/team', name: 'app_team1')]
    public function team(): Response
    {
        return $this->render('gym-html-template/team.html.twig');
    }

    #[Route('/contact', name: 'app_contact1')]
    public function contact(): Response
    {
        return $this->render('gym-html-template/contact.html.twig');
    }

    #[Route('/class', name: 'app_class')]
    public function classes(): Response
    {
        return $this->render('gym-html-template/class.html.twig');
    }

    #[Route('/testimonial', name: 'app_testimonial')]
    public function testimonial(): Response
    {
        return $this->render('gym-html-template/testimonial.html.twig');
    }
}