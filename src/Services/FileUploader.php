<?php
namespace App\Services;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class FileUploader
{
  private $targetDirectory;
  /**
   * Constructor for FileUploader Service class.
   *
   * @param Symfony\Component\String\Slugger\SluggerInterface $slugger
   *   Slugger to handle generation and storage of valid/safe filenames.
   */
  public function __construct(private SluggerInterface $slugger) {
  }

  /**
   * Function to upload file.
   *
   * @param UploadedFile $file
   *   File to be uploaded.
   * 
   * @return string
   *   Returns the final filename of the uploaded file.
   */
  public function upload(UploadedFile $file): string {
    $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
    $safeFilename = $this->slugger->slug($originalFilename);
    $fileName = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();
    try {
      $file->move($this->getTargetDirectory(), $fileName);
    } 
    catch (FileException $e) {
      dd($e);
    }

    return $fileName;
  }

  /**
   * Function to set target directory for uploading the file.
   *
   * @param string $target
   *   Target Directory in string format.
   * 
   * @return void
   */
  public function setTargetDirectory(string $target) {
    $this->targetDirectory = $target;
  }

  /**
   * Function to get target directory for uploading the file.
   * 
   * @return string
   *   Returns the target directory that has been set.
   */
  public function getTargetDirectory(): string {
    return $this->targetDirectory;
  }
}