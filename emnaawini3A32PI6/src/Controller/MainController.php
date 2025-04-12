<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    #[Route('/home', name: 'app_home')]
    public function index(): Response
    {
        return $this->render('home/index.html.twig', [
            'controller_name' => 'MainController',
        ]);
    }

    #[Route('/about', name: 'app_about')]
    public function about(): Response
    {
        return $this->render('home/about.html.twig');
    }

    #[Route('/classes', name: 'app_classes')]

    public function classes(): Response
    {
        return $this->render('home/classes.html.twig', );
           
        
    }

    #[Route('/trainers', name: 'app_trainers')]
    public function trainers(): Response
    {
       
        return $this->render('home/trainers.html.twig', 
            
        );
    }

    #[Route('/blog', name: 'app_blog_grid1')]
    public function blogGrid(): Response
    {

        return $this->render('home/blog_grid.html.twig', 
           
        );
    }

   

    #[Route('/testimonial', name: 'app_testimonial')]
    public function testimonial(): Response
    {
        // Exemple de données pour les témoignages
        $testimonials = [
            ['text' => 'Dolores sed duo clita tempor justo dolor et stet lorem kasd labore dolore lorem ipsum.', 'name' => 'Client Name', 'profession' => 'Profession', 'image' => 'testimonial-1.jpg'],
            ['text' => 'Dolores sed duo clita tempor justo dolor et stet lorem kasd labore dolore lorem ipsum.', 'name' => 'Client Name', 'profession' => 'Profession', 'image' => 'testimonial-2.jpg'],
        ];

        return $this->render('home/testimonial.html.twig', [
            'testimonials' => $testimonials,
        ]);
    }

    #[Route('/contact', name: 'app_contact')]
    public function contact(): Response
    {
        return $this->render('home/contact.html.twig');
    }
}