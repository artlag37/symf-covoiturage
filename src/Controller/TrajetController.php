<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Trajet;
use App\Entity\Ville;
use DateTime;

class TrajetController extends AbstractController
{
    /**
     * @Route("/trajet/ajout", name="ajoutTrajet
     *",methods={"POST"})    
     */

    public function ajoutTrajet(Request $request)
    {
        if ($request->isMethod('post')) {
            // On instancie une nouvelle marque
            $trajet = new Trajet();

            // On décode les données envoyées
            $donnees = json_decode($request->getContent());

            // On hydrate l'objet
            $villeDep = $this->getDoctrine()->getRepository(Ville::class)->find($donnees->ville_dep_id);
            $trajet->setVilleDep($villeDep);
            $villeArr = $this->getDoctrine()->getRepository(Ville::class)->find($donnees->ville_arr_id);
            $trajet->setVilleArr($villeArr);
            $trajet->setNbKms($donnees->NbKms);
            $date = new DateTime(date('Y-m-d H:00:00'));
            $trajet->setDateTrajet($date);

            // On sauvegarde en bdd
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($trajet);
            $entityManager->flush();

            // On retourne la confirmation
            return new Response('ok', 201);
        } else{
            return new Response('Failed', 404);
        }
    }

    /**
     * @Route("/trajet", name="trajet")
     */
    public function liste(Request $request)
    { 
        if ($request->isMethod('get')) {

            //recuperation du repository grace au manager
            $em = $this->getDoctrine()->getManager();
            $trajetRepository = $em->getRepository(Trajet::class);
            //trajetRepository herite de servciceEntityRepository ayant les methodes pour recuperer les données de la bdd
            $listeTrajets = $trajetRepository->findAll();
            $resultat = [];
            foreach ($listeTrajets as $traj) {
                array_push($resultat, ["id"=>$traj->getId(),"personne"=>$traj->getPersonne(), "ville de départ"=>$traj->getVilleDep()->getNom(), "ville d'arrivée"=>$traj->getVilleArr()->getNom(), "Distance"=>$traj->getNbKms(), "Date du trajet"=>$traj->getDateTrajet()]);
            }
            $reponse = new JsonResponse($resultat);
    
            return $reponse;
        } else {
            return new Response('Failed', 404);
        }
    }

        /**
     * @Route("/trajet/suppr/{id}", 
     * name="deleteTrajet",requirements={"id"="[0-9]{1,5}"})    
     */

    public function delete(Request $request, $id)
    {
        if ($request->isMethod('delete')){

            //récupération du Manager  et du repository pour accéder à la bdd
            $em = $this->getDoctrine()->getManager();
            $trajetRepository = $em->getRepository(Trajet::class);
            //requete de selection
            $traj = $trajetRepository->find($id);
            //suppression de l'entity
            $em->remove($traj);
            $em->flush();
            $resultat = ["ok"];
            $reponse = new JsonResponse($resultat);
            return $reponse;
        } else {
            return new Response('Failed', 404);
        }
    }
}
