<?php
// src/Controller/WildController.php
namespace App\Controller;

use App\Entity\Episode;
use App\Entity\Season;
use App\Entity\Category;
use App\Entity\Program;
use App\Repository\EpisodeRepository;
use App\Repository\SeasonRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;



Class WildController extends AbstractController
{
    /**
     * Show all rows from Program’s entity
     *
     * @Route("/", name="index")
     * @return Response A response instance
     */
    public function index(): Response
    {
        $programs = $this->getDoctrine()
            ->getRepository(Program::class)
            ->findAll();

        if (!$programs) {
            throw $this->createNotFoundException(
                'No program found in program\'s table .'
            );
        }

        return $this->render(
            'wild/index.html.twig',
            ['programs' => $programs]
        );
    }

    /**
     * Getting a program with a formatted slug for title
     *
     * @param string $slug The slugger
     * @Route("/show/{slug<^[a-z0-9-]+$>}", defaults={"slug" = null}, name="show")
     * @return Response
     */
    public function show(?string $slug):Response
    {
        if (!$slug) {
            throw $this
                ->createNotFoundException('No slug has been sent to find a program in program\'s table.');
        }
        $slug = preg_replace(
            '/-/',
            ' ', ucwords(trim(strip_tags($slug)), "-")
        );
        $program = $this->getDoctrine()
            ->getRepository(Program::class)
            ->findOneBy(['title' => mb_strtolower($slug)]);
        if (!$program) {
            throw $this->createNotFoundException(
                'No program with '.$slug.' title, found in program\'s table.'
            );
        }

        return $this->render('wild/show.html.twig', [
            'program' => $program,
            'slug'  => $slug,
        ]);
    }

    /**
     * @Route("/category/{categoryName}", name="show_category")
     * @param string $categoryName
     * @return Response
     */
    public function showByCategory(string $categoryName):Response
    {
        if (!$categoryName) {
            throw $this->createNotFoundException('No category name has been sent to find a program in program\'s table.');
        }
        $category = $this->getDoctrine()->getRepository(Category::class)
            ->findOneBy(['name' => $categoryName]);
        $programs = $this->getDoctrine()->getRepository(Program::class)
            ->findBy(['category' => $category],['id'=>'DESC'],3);
        if (!$programs) {
            throw $this->createNotFoundException(
                'No category with '.$categoryName.' name, found in category\'s table.'
            );
        }

        return $this->render("wild/category.html.twig",['programs' => $programs,
                                                        'category' => $category,
        ]);
    }
    /**
     * @Route("/program/{programName<[a-z0-9-]+$>}", defaults={"programName" = null}, name="show_program")
     * @param string $programName
     * @return Response
     */
    public function showByProgram(?string $programName): Response
    {
        if (!$programName) {
            throw $this
                ->createNotFoundException('No slug has been sent to find a program in program\'s table');
        }
        $programName = preg_replace(
            '/-/',
            ' ', ucwords(trim(strip_tags($programName)), "-")
        );
        $repositoryProgram = $this->getDoctrine()->getRepository(Program::class);
        $program = $repositoryProgram->findOneBy(
            ['title' =>mb_strtolower($programName)
        ]);
        $seasonsProgram = $program->getSeasons();

        return $this->render('wild/program.html.twig', [
            'program' => $program,
            'seasons' =>$seasonsProgram,
        ]);
    }

    /**
     * @param int $id
     * @return Response
     * @Route("/season/{id}", name="show_season", requirements={"id"= "\d+"})
     */
    public function showBySeason(int $id, SeasonRepository $seasonRepository): Response
    {
        
        $season = $seasonRepository->find($id);
            
       
        if ($season === null) {
            throw $this->createNotFoundException(
                'No season with id '.$id.' , found in Season\'s table.'
            );
        }
        return $this->render('season/show.html.twig', [
            'season' => $season,
        ]);
    }

    /**
     * @param int $id
     * @return Response
     * @Route("/episode/{id}", name="show_episode", requirements={"id"= "\d+"})
     */
    public function showByEpisode(int $id, EpisodeRepository $episodeRepository): Response
    {
        $episode = $episodeRepository->find($id);

        if ($episode === null) {
            throw $this->createNotFoundException(
                'No episode with id ' .$id. ' found in Season\'s table.'
            );
        }

        $season = $episode->getSeason();
        $program = $season->getProgram();

        return $this->render('wild/episode.html.twig', [
            'program' => $program,
            'episode' => $episode,
            'season' => $season,
        ]);
    }
    /**
     * @param int $id
     * @return Response
     * @Route("/episode/{id<^[0-9-]+$>}", defaults={"id" = null}, name="show_episode")
     */
    public function showEpisode(Episode $episode): Response
    {
        return $this->render('wild/episode.html.twig', ['episode' =>$episode]);
    }
} 