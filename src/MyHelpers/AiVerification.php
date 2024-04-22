<?php

namespace App\MyHelpers;

use App\Entity\AiResult;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;

class AiVerification
{
    private $aiDataHolder;


    public function run($obj): AiDataHolder
    {
        $this->aiDataHolder=new AiDataHolder();;
        $this->getAllDesc($obj['images']);
        $this->compareDescWithTitleAndCategory($this->aiDataHolder->getDescriptions(),$obj);
        return $this->aiDataHolder;
    }


//    private function saveData($obj): void
//    {
//        $aiResult = new AiResult();
//        $aiResultServes=new AiResultServes();
//
//        $serializer = new Serializer([new ObjectNormalizer()], [new JsonEncoder()]);
//        $serializedData = $serializer->serialize($this->aiDataHolder, 'json');
//
//        $aiResult->setBody($serializedData);
//        $aiResult->setIdProduct($obj['product']);
//
//    }


    private function getAllDesc($images_url): void
    {
        $result=[];
        for($i=0;$i<sizeof($images_url);$i++){
            $result[]=$this->generateImageDescription($images_url[$i]);
        }
        $this->aiDataHolder->setDescriptions($result);
    }

    private function compareDescWithTitleAndCategory($descriptions,$obj): void
    {
        $result1=[];
        $result2=[];
        for($i=0;$i<sizeof($descriptions);$i++){
            $result1[]=$this->getTitleValidation($descriptions[$i],$obj['product']->getName());
            $result2[]=$this->getCategoryValidation($descriptions[$i],$obj['product']->getCategory());
        }
        $this->aiDataHolder->setTitleValidation($result1);
        $this->aiDataHolder->setCategoryValidation($result2);

    }

    private function getTitleValidation($desc,$title): string
    {
        return $this->Http('get-title_validation?desc='.$desc.'&title='.$title);
    }

    private function getCategoryValidation($desc,$category): string
    {
        return $this->Http('get-category_validation?desc='.$desc.'&category='.$category);
    }

    private function generateImageDescription($image_url): string
    {
        $absolute_path='C:\Users\omar salhi\Desktop\PIDEV\citiezenHub_webapp\public\usersImg\\'.$image_url;
        return $this->Http('get-product_image_descreption?image_url='.$absolute_path);
    }


    private function Http($url): string
    {
        $client = HttpClient::create();
        $response = $client->request('POST','http://127.0.0.1:5000/'.$url);
        $substringsToRemove = ['\"', '""\\', '"\n', '"', '\n'];
        $content = str_replace($substringsToRemove, "", $response->getContent());
        return $content;
    }


}