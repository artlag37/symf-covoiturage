<?php

namespace App\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Personne;
use App\Entity\User;
use App\Entity\Ville;
use App\Entity\Voiture;

class PersonneController extends AbstractController
{

    /**
     * @Route("/personne/ajout", name="ajoutPersonne",methods={"POST"})    
     */

    public function ajoutPersonne(Request $request)
    {

        if ($request->isMethod('post')) {

            // On instancie une nouvelle personne
            $pers = new Personne();

            // On décode les données envoyées
            $donnees = json_decode($request->getContent());

            // On hydrate l'objet
            $pers->setNom($donnees->nom);
            $pers->setPrenom($donnees->prenom);
            $ville = $this->getDoctrine()->getRepository(Ville::class)->find($donnees->ville_id);
            $pers->setVille($ville);
            $voiture = $this->getDoctrine()->getRepository(Voiture::class)->find($donnees->voiture_id);
            $pers->setVoiture($voiture);
            $pers->setTel($donnees->tel);
            $pers->setEmail($donnees->email);
            $user = $this->getDoctrine()->getRepository(User::class)->find($donnees->user_id);
            $pers->setUser($user);

            // On sauvegarde en bdd
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($pers);
            $entityManager->flush();

            // On retourne la confirmation
            return new Response('ok', 201);
        } else {
            return new Response('Failed', 404);
        }
    }

    /**
     * @Route("/personne", name="personne")
     */
    public function liste(Request $request)
    { 
        if ($request->isMethod('get')) {
            //recuperation du repository grace au manager
            $em = $this->getDoctrine()->getManager();
            $personneRepository = $em->getRepository(Personne::class);
            //personneRepository herite de servciceEntityRepository ayant les methodes pour recuperer les données de la bdd
            $listePersonnes = $personneRepository->findAll();
            $resultat = [];
            foreach ($listePersonnes as $pers) {
                array_push($resultat, ["id"=>$pers->getId(),"nom"=>$pers->getNom(), "prenom"=>$pers->getPrenom(), "date de naissance"=>$pers->getDateNaiss(), "ville"=>$pers->getVille()->getNom(), "telephone"=>$pers->getTel(), "email"=>$pers->getEmail(), "voiture"=>["marque"=>$pers->getVoiture()->getMarque()->getNom(),"modele"=>$pers->getVoiture()->getModele()], "user"=>["id"=>$pers->getUser()->getId(),"username"=>$pers->getUser()->getUsername()]]);
            }
            $reponse = new JsonResponse($resultat);
    
    
            return $reponse;
        } else {
            return new Response('Failed', 404);
        }
    }

    /**
     * @Route("/personne/suppr/{id}", 
     * name="deletePersonne",requirements={"id"="[0-9]{1,5}"})    
     */

    public function delete(Request $request, $id)
    {

        if ($request->isMethod('delete')){
            //récupération du Manager  et du repository pour accéder à la bdd
            $em = $this->getDoctrine()->getManager();
            $personneRepository = $em->getRepository(Personne::class);
            //requete de selection
            $pers = $personneRepository->find($id);
            //suppression de l'entity
            $em->remove($pers);
            $em->flush();
            $resultat = ["ok"];
            $reponse = new JsonResponse($resultat);
            return $reponse;
        } else {
            return new Response('Failed', 404);
        }
    }
}
