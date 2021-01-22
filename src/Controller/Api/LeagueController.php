<?php

namespace App\Controller\Api;

use App\Entity\League;
use App\Repository\LeagueRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;


class LeagueController extends AbstractController
{


    /**
    * @Route("/api/leagues", name="api_league")
    */

    public function list(
        LeagueRepository $leagueRepository
    ){   
        $data = $leagueRepository->findAll();
        $response = new JsonResponse();
        $data = $this->JsonSuccess($data);
        return new Response($data);
    }

    /**
     * @Route("/api/leagues/find")
     */
    public function find(   
        Request $request,
        EntityManagerInterface $em
    ){
        $em = $this->getDoctrine()->getManager();
        $sql = " Select * from league where name = :name ";
        $statement = $em->getConnection()->prepare($sql);
        $statement->bindValue('name', $request->get('name'));
        $statement->execute();
        $result = $statement->fetchAll();
        $data = $this->JsonSuccess($result);
        return new Response($data);
    }


    /**
     * @Route("/api/leagues/store", name="store_league", methods="post")
     */ 

    public function postAction(
        EntityManagerInterface $em,
        Request $request
    ){
        $league = new League();
        $league->setName($request->get('name'));
        $em->persist($league);
        $em->flush();

        return new Response("Exito");
    }   

    /**
     * @Route("/api/leagues/edit")
     */
    public function update(
        Request $request,
        EntityManagerInterface $em
    ){
        $league = $em->getRepository(League::class)->find($request->get('id'));
        if (!$league) {
            throw $this->createNotFoundException(
                'No item found for id '.$id
            );
        }
        $league->setName($request->get('name'));
        $em->flush();
        return new Response($data);
    }

     /**
     * @Route("/api/leagues/delete")
     */
     public function delete(
        Request $request,
        EntityManagerInterface $em
    ){
        $league = $em->getRepository(League::class)->find($request->get('id'));
        if (!$league) {
            throw $this->createNotFoundException(
                'No item found for id '.$id
            );
        }
        $em->remove($league);
        $em->flush();
        return new Response("Exito");
    }

    public static function JsonSuccess($data){
        $encoders = [new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);
        $data = $serializer->serialize(
            [
                'status' => 'success',
                'data' => $data
            ], 'json');
        return $data;
    }

    public static function JsonError($data){
        $encoders = [new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);
        $data = $serializer->serialize(
            [
                'status' => 'error',
                'data' => $data
            ], 'json');
        return $data;
    }

}
