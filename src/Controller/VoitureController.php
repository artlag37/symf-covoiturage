<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Voiture;
use App\Entity\Marque;
use App\Repository\MarqueRepository;

class VoitureController extends AbstractController
{
    /**
     * @Route("/voiture/ajout", name="ajout",methods={"POST"})    
     */

    public function ajoutVoiture(Request $request)
    {
        if ($request->isMethod('post')) {
            // On instancie une nouvelle voiture
            $voiture = new Voiture();

            // On décode les données envoyées
            $donnees = json_decode($request->getContent());

            // On hydrate l'objet
            $marque = $this->getDoctrine()->getRepository(Marque::class)->find($donnees->marque_id);
            $voiture->setMarque($marque);
            $voiture->setNbPlaces($donnees->nbPlaces);
            $voiture->setModele($donnees->modele);

            // On sauvegarde en bdd
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($marque);
            $entityManager->flush();

            // On retourne la confirmation
            return new Response('ok', 201);
        } else{
            return new Response('Failed', 404);
        }

    }

    /**
     * @Route("/voiture", name="voiture")
     */
    public function Voiture(Request $request)
    { 
        if ($request->isMethod('get')) {

            //recuperation du repository grace au manager
            $em = $this->getDoctrine()->getManager();
            $VoitureRepository = $em->getRepository(Voiture::class);
            //personneRepository herite de servciceEntityRepository ayant les methodes pour recuperer les données de la bdd
            $listeVoitures = $VoitureRepository->findAll();
            $resultat = [];
            foreach ($listeVoitures as $voit) {
                array_push($resultat,["id"=>$voit->getId(),"marque"=>$voit->getMarque()->getNom(),"modele"=>$voit->getModele(),"Nombre de place"=>$voit->getNbPlaces()]);
            }
            $reponse = new JsonResponse($resultat);

            return $reponse;
        } else {
            return new Response('Failed', 404);
        }
    }


    /**
     * @Route("/voiture/suppr/{id}", 
     * name="deleteVoiture",requirements={"id"="[0-9]{1,5}"})    
     */

    public function delete(Request $request, $id)
    {

        if ($request->isMethod('delete')){

            //récupération du Manager  et du repository pour accéder à la bdd
            $em = $this->getDoctrine()->getManager();
            $VoitureRepository = $em->getRepository(Voiture::class);
            //requete de selection
            $voit = $VoitureRepository->find($id);
            //suppression de l'entity
            $em->remove($voit);
            $em->flush();
            $resultat = ["ok"];
            $reponse = new JsonResponse($resultat);
            return $reponse;
        } else {
            return new Response('Failed', 404);
        }
    }
}
