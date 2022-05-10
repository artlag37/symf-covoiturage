<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Inscription;
use App\Entity\Personne;
use App\Entity\Trajet;

class InscriptionController extends AbstractController
{
    /**
     * @Route("/inscription/ajout", name="ajoutInscription",methods={"POST"})    
     */

    public function ajoutMarque(Request $request)
    {
        if ($request->isMethod('post')) {
            // On instancie une nouvelle inscription
            $insc = new Inscription();

            // On décode les données envoyées
            $donnees = json_decode($request->getContent());

            // On hydrate l'objet
            $pers = $this->getDoctrine()->getRepository(Personne::class)->find($donnees->personne_id);
            $insc->setPersonne($pers);
            $traj = $this->getDoctrine()->getRepository(Trajet::class)->find($donnees->trajet_id);
            $insc->setTrajet($traj);
            // On sauvegarde en bdd
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($insc);
            $entityManager->flush();

            // On retourne la confirmation
            return new Response('ok', 201);
        } else {
            return new Response('Failed', 404);
        }
    }

    /**
     * @Route("/inscription", name="inscription")
     */
    public function inscription(Request $request)
    { 
        if ($request->isMethod('get')) {

            //recuperation du repository grace au manager
            $em = $this->getDoctrine()->getManager();
            $inscriptionRepository = $em->getRepository(Inscription::class);
    
            //inscriptionRepository herite de servciceEntityRepository ayant les methodes pour recuperer les données de la bdd
            $listeInscriptions = $inscriptionRepository->findAll();
            $resultat = [];
            foreach ($listeInscriptions as $inscr) {
                array_push($resultat, ["id" => $inscr->getId(), "personne" =>["nom" =>$inscr->getPersonne()->getNom(),"prenom" =>$inscr->getPersonne()->getPrenom()], "trajet" =>["Date" =>$inscr->getTrajet()->getDateTrajet(),"Ville de départ" =>$inscr->getTrajet()->getVilleDep()->getNom(),"Ville d'arrivée" =>$inscr->getTrajet()->getVilleArr()->getNom(),"Distance" =>$inscr->getTrajet()->getNbKms()]]);
            }
            $reponse = new JsonResponse($resultat);
    
    
            return $reponse;
        } else {
            return new Response('Failed', 404);
        }
    }

    /**
     * @Route("/inscription/suppr/{id}", 
     * name="deleteInscription",requirements={"id"="[0-9]{1,5}"})    
     */

    public function delete(Request $request, $id)
    {

        if ($request->isMethod('delete')){

            //récupération du Manager  et du repository pour accéder à la bdd
            $em = $this->getDoctrine()->getManager();
            $InscriptionRepository = $em->getRepository(Inscription::class);
            //requete de selection
            $insc = $InscriptionRepository->find($id);
            //suppression de l'entity
            $em->remove($insc);
            $em->flush();
            $resultat = ["ok"];
            $reponse = new JsonResponse($resultat);
            return $reponse;
        } else {
            return new Response('Failed', 404);
        }
    }

}
