<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Marque;

class MarqueController extends AbstractController
{

    /**
     * @Route("/marque/ajout", name="ajoutMarque",methods={"POST"})    
     */

    public function ajoutMarque(Request $request)
    {
        if ($request->isMethod('post')) {
            // On instancie une nouvelle marque
            $marque = new Marque();

            // On décode les données envoyées
            $donnees = json_decode($request->getContent());

            // On hydrate l'objet
            $marque->setNom($donnees->nom);

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
     * @Route("/marque", name="marque")
     */
    public function marque(Request $request)
    { 
        if ($request->isMethod('get')) {

            //recuperation du repository grace au manager
            $em = $this->getDoctrine()->getManager();
            $marqueRepository = $em->getRepository(Marque::class);
    
            //personneRepository herite de servciceEntityRepository ayant les methodes pour recuperer les données de la bdd
            $listeMarques = $marqueRepository->findAll();
            $resultat = [];
            foreach ($listeMarques as $marq) {
                array_push($resultat, ["id"=>$marq->getId(),"nom"=>$marq->getNom()]);
            }
            $reponse = new JsonResponse($resultat);
    
            return $reponse;

        } else {
            return new Response('Failed', 404);
        }
    }


    /**
     * @Route("/marque/suppr/{id}", 
     * name="deleteMarque",requirements={"id"="[0-9]{1,5}"})    
     */

    public function delete(Request $request, $id)
    {

        if ($request->isMethod('delete')){

            //récupération du Manager  et du repository pour accéder à la bdd
            $em = $this->getDoctrine()->getManager();
            $marqueRepository = $em->getRepository(Marque::class);
            //requete de selection
            $marq = $marqueRepository->find($id);
            //suppression de l'entity
            $em->remove($marq);
            $em->flush();
            $resultat = ["ok"];
            $reponse = new JsonResponse($resultat);
            return $reponse;
        }  else {
            return new Response('Failed', 404);
        }
    }
}
