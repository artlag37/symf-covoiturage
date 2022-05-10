<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Ville;

class VilleController extends AbstractController
{
    /**
     * @Route("/ville/ajout", name="ajoutVille",methods={"POST"})    
     */

    public function ajoutVille(Request $request)
    {
        if ($request->isMethod('post')) {
            // On instancie une nouvelle marque
            $ville = new Ville();

            // On décode les données envoyées
            $donnees = json_decode($request->getContent());

            // On hydrate l'objet
            $ville->setNom($donnees->nom);
            $ville->setCodepostal($donnees->codepostal);

            // On sauvegarde en bdd
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($ville);
            $entityManager->flush();

            // On retourne la confirmation
            return new Response('ok', 201);
        } else{
            return new Response('Failed', 404);
        }

    }

    /**
     * @Route("/ville", name="ville")
     */
    public function ville(Request $request)
    { 
        if ($request->isMethod('get')) {


            //recuperation du repository grace au manager
            $em = $this->getDoctrine()->getManager();
            $villeRepository = $em->getRepository(Ville::class);
            //personneRepository herite de servciceEntityRepository ayant les methodes pour recuperer les données de la bdd
            $listeVilles = $villeRepository->findAll();
            $resultat = [];
            foreach ($listeVilles as $vil) {
                array_push($resultat, ["id"=>$vil->getId(),"nom"=>$vil->getNom(),"codepostal"=>$vil->getCodepostal()]);
            }
            $reponse = new JsonResponse($resultat);
    
    
            return $reponse;
        } else {
            return new Response('Failed', 404);
        }
    }


    /**
     * @Route("/ville/suppr/{id}", 
     * name="deleteVille",requirements={"id"="[0-9]{1,5}"})    
     */

    public function delete(Request $request, $id)
    {
        if ($request->isMethod('delete')){

            //récupération du Manager  et du repository pour accéder à la bdd
            $em = $this->getDoctrine()->getManager();
            $villeRepository = $em->getRepository(Ville::class);
            //requete de selection
            $vil = $villeRepository->find($id);
            //suppression de l'entity
            $em->remove($vil);
            $em->flush();
            $resultat = ["ok"];
            $reponse = new JsonResponse($resultat);
            return $reponse;
        } else {
            return new Response('Failed', 404);
        }
    }
}
