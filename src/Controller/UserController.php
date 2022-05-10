<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\Entity\User;

class UserController extends AbstractController
{
    /**
     * @Route("/user/ajout", name="ajoutUser
     *",methods={"POST"})    
     */

    public function ajoutUser(Request $request, UserPasswordEncoderInterface $encoder)
    {
        if ($request->isMethod('post')) {
            // On instancie un nouveau user
            $user = new User();

            // On décode les données envoyées
            $donnees = json_decode($request->getContent());

            // On hydrate l'objet
            $user->setUsername($donnees->username);
            $user->setRoles($donnees->role);
            $user->setPassword($encoder->encodePassword($user, $donnees->password));
            $user->setApiToken($donnees->apiToken);

            // On sauvegarde en bdd
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            // On retourne la confirmation
            return new Response('ok', 201);
        } else {
            return new Response('Failed', 404);
        }
    }

    /**
     * @Route("/users", name="users")
     */

    public function user(Request $request)
    {
        if ($request->isMethod('get')) {
            //recuperation du repository grace au manager
            $em = $this->getDoctrine()->getManager();
            $userRepository = $em->getRepository(User::class);
            //personneRepository herite de servciceEntityRepository ayant les methodes pour recuperer les données de la bdd
            $listeUsers = $userRepository->findAll();
            $resultat = [];
            foreach ($listeUsers as $u) {
                array_push($resultat, ["id" => $u->getId(), "username" => $u->getUserName(), "apiToken" => $u->getApiToken()]);
            }
            $reponse = new JsonResponse($resultat);


            return $reponse;
        } else {
            return new Response('Failed', 404);
        }
    }


    /**
     * @Route("/user/suppr/{id}", 
     * name="deleteUser",requirements={"id"="[0-9]{1,5}"})    
     */

    public function delete(Request $request, $id)
    {
        if ($request->isMethod('delete')) {

            //récupération du Manager  et du repository pour accéder à la bdd
            $em = $this->getDoctrine()->getManager();
            $userRepository = $em->getRepository(User::class);
            //requete de selection
            $u = $userRepository->find($id);
            //suppression de l'entity
            $em->remove($u);
            $em->flush();
            $resultat = ["ok"];
            $reponse = new JsonResponse($resultat);
            return $reponse;
        } else {
            return new Response('Failed', 404);
        }
    }

    /**
     * @Route("/login/{username}/{password}", name="login",requirements={"username"="[a-z]{4,30}", "password"="[a-z]{4,30}"})
     */
    public function login(Request$request, $username, $password, UserPasswordEncoderInterface $passwordEncoder)
    {
        //TODO: login prend en argument un login et un mot de passe , puis retourne un token

        if($request->isMethod('get')){

            //récupération du Manager  et du repository pour accéder à la bdd
            $em = $this->getDoctrine()->getManager();
    
            //récupération du user dans le repository
            $userRepository = $em->getRepository(User::class);
            $user = $userRepository->findOneBy(['username' => $username]);
     
            //Si le mot de passe est valide, on retourne un token
            if ($passwordEncoder->isPasswordValid($user, $password )) {
    
                $apiToken = $user->getApiToken();
    
                // return $this->em->getRepository(User::class)->findOneBy(['apiToken' => $apiToken]);
                $resultat=[$apiToken];
            } else {
                $resultat=["Log failed"];
            }

            $reponse = new JsonResponse($resultat); 
            return $reponse;

        }
    }

    /**
     * @Route("/register/{username}/{password}", name="app_register",requirements={"username"="[a-z]{4,30}", "password"="[a-z]{4,30}"})
     */
    public function register(Request$request, $username, $password, UserPasswordEncoderInterface $passwordEncoder)
    {
        //TODO: register , enregistre un login et un mot de passe et retourne un token

        if($request->isMethod('get')){
            //On instancie un user puis on set son username et mot de passe
            $user = new User;
            $user->setUsername($username);
            $encodePassword = $passwordEncoder->encodePassword($user, $password);
            $user->setPassword($encodePassword);
    
            //On génère un token puis on le set 
            $token = base_convert(hash('sha256', time() . mt_rand()), 16, 36);
            $user->setApiToken($token);
    
            // On sauvegarde en bdd
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();
    
            //On récupère le token enregistré précédemment pour le retourner au front
            $apiToken = $user->getApiToken();

            $resultat=[$apiToken];
        } else {
            $resultat=["Register failed"];
        }

        $reponse = new JsonResponse($resultat); 
        return $reponse;

    }
}
