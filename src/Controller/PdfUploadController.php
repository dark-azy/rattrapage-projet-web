<?php

namespace App\Controller;


use App\Entity\Etudiant;
use App\Entity\Pdf;
use App\Form\PdfCVType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Filesystem\Filesystem;

class PdfUploadController extends AbstractController
{
    #[Route('/profile/CV', name: 'app_cv')]
    public function new(
        Request $request,
        SluggerInterface $slugger,
        EntityManagerInterface $entityManager,
        #[Autowire('%kernel.project_dir%/public/uploads')] string $pdfDirectory
    ): Response
    {        
        $user = $this->getUser();
        if (!$user || !($user instanceof Etudiant)) {
            $this->addFlash('error', 'Vous devez être connecté en tant qu\'étudiant pour accéder à cette page.');
            return $this->redirectToRoute('app_login');
        }
        $pdf = new Pdf();

        $form = $this->createForm(PdfCVType::class, $pdf);
        $form->handleRequest($request);
        


        $pdf->setEtudiant($user);

        

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $cvFile */
            $cvFile = $form->get('CV')->getData();

            // this condition is needed because the 'CV' field is not required
            // so the PDF file must be processed only when a file is uploaded
            if ($cvFile) {
                $originalFilename = pathinfo($cvFile->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$cvFile->guessExtension();

                // Move the file to the directory where pdf are stored
                try {
                    $cvFile->move($pdfDirectory, $newFilename);
                } catch (FileException $e) {
                    // Add flash message for error
                    $this->addFlash('error', 'Une erreur est survenue lors du téléchargement du fichier.');
                    // You might want to log this exception
                }

                // updates the 'filename' property to store the PDF file name
                // instead of its contents
                $pdf->setFilename($newFilename);
                
                // Persist the entity to database
                $entityManager->persist($pdf);
                $entityManager->flush();
                
                $this->addFlash('success', 'Votre CV a été téléchargé avec succès.');
                
                // Redirect to same page to avoid form resubmission
                return $this->redirectToRoute('app_cv');
            }
        }

        $userPdfs = $entityManager->getRepository(Pdf::class)->findBy(
            ['etudiant' => $user],
            ['id' => 'DESC']
        );

        return $this->render('pdf_upload/index.html.twig', [
            'form' => $form,
            'pdfs' => $userPdfs, // Passer la liste complète des PDFs
            'pdf' => !empty($userPdfs) ? $userPdfs[0] : null
        ]);
    }
    #[Route('/pdf/delete/{id}', name: 'app_pdf_delete')]
    public function delete(
        Pdf $pdf,
        EntityManagerInterface $entityManager,
        #[Autowire('%kernel.project_dir%/public/uploads')] string $pdfDirectory,
        Filesystem $filesystem
    ): Response
    {
        // Vérifier que l'utilisateur connecté est bien le propriétaire du PDF
        $user = $this->getUser();
        if (!$user || $pdf->getEtudiant() !== $user) {
            $this->addFlash('error', 'Vous n\'êtes pas autorisé à supprimer ce document.');
            return $this->redirectToRoute('app_cv');
        }

        // Récupérer le nom du fichier avant de supprimer l'entité
        $filename = $pdf->getFilename();

        // Supprimer l'entité de la base de données
        $entityManager->remove($pdf);
        $entityManager->flush();

        // Supprimer le fichier physique
        $filePath = $pdfDirectory . '/' . $filename;
        if ($filesystem->exists($filePath)) {
            $filesystem->remove($filePath);
        }

        // Ajouter un message flash de succèss
        $this->addFlash('success', 'Le document a été supprimé avec succès.');

        // Rediriger vers la page des CV
        return $this->redirectToRoute('app_cv');
    }
}
?>