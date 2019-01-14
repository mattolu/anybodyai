<?php

namespace App\Http\Controllers;

use Validator;
//use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;
use App\Http\Controllers;
use Illuminate\Support\Collection;

class SearchController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    //private $request;
    // public function __construct()
    // {
    //     //
    //     $this->request= $request;
    // }

  
    public function search(Request $request){
        //To Calculate the weight of the searches across the returned json from the server
        $sum=0; 
           
        $urlImgArray=[];
        $inputArray = [];
        $titleArray = [];
        $urlTitle =[];
        $titleArray1 = [];
        $snippetArray = [];
        $urlTitle1 =[];
        $imageArray = [];
        $newTit = [];
        $resultArray=[];
        $occupationArray =[];
        $descriptionArray = [];
        //$jsonArrayNew = ['profile'=>null, 'likely_occupation' =>null, 'description' =>null, 'img_src' =>null]; 
        $jsonArrayNew1=[];
        $jsonArrayNew2=[];
        $jsonArrayNew3=[];
        $jsonArrayNew4=[];
        


    

        $validator = Validator::make($request->all(), [
            'search_word' => 'required'
        ]);
        if ($validator->fails()){
            return response()->json([
                'error'=>[
                    'success' => false,
                    'status' =>400,
                    'message' => $validator->errors()->all()
                        ]]);
        } else{
            
           // var_dump($query);
            $search = $request->search_word;

            $query = strtolower($request->search_word);
            //The API URL for getting the information 
             
           // $url= "https://www.googleapis.com/customsearch/v1?key=AIzaSyBty0n30fjS0Avk-VAlRD4EwLslk5231Ik&cx=018412839258995437894:78awquivuuq&q=".$query."&fields=items(title,snippet,pagemap/cse_image,pagemap/metatags,pagemap/hcard)" ;
        $url= "https://www.googleapis.com/customsearch/v1?key=AIzaSyB5WOpV4_-J6QK2XbldcQ-BVgJl6FotTTo&cx=009130427976801447388:idrpxx1qx8c&q=".$query."&fields=items(title,snippet,pagemap/cse_image,pagemap/metatags,pagemap/hcard)" ;
           //$url= "https://www.googleapis.com/customsearch/v1?key=AIzaSyD777ZEvs4UqMZ7kxAv-w98TK1E4hdGoII&cx=018412839258995437894:fgnrg7qhlqy&q=".$query."&fields=items(title,snippet,pagemap/cse_image)" ;
           //$url= "https://www.googleapis.com/customsearch/v1?key=AIzaSyATF24vZ97D7lbdQ1zPuxfJcGvJDQhLh0A&cx=009130427976801447388:athkuwtwhli&q=".$query."&fields=items(title,snippet,pagemap/cse_image)" ;

            $client = new \GuzzleHttp\Client();
            $request = $client->get($url);
            $response = $request->getBody()->getContents();
            //return $response;
            

            //opening of result from the CURL call
            if(!empty($response)) {
                $jsonResult = json_decode($response, true);
                $resultArray= $jsonResult['items'];
                //print_r($resultArray);
                
                if ($resultArray==NULL){
                    return json_encode([
                        'error' => 'Daily limit exceeded. Please try again tomorrow'
                    ]);
                    
                }else{
                        
                        $arrlength = count($resultArray); //getting the total number of results in the array

            //Checking for the number of input with space as the delimiter
                            if (strpos($search, ' ')){
                            $splitr= explode(" ", $search);//if there is space, then split it into an array
                            $inputArray[0] = $splitr[1];
                            $inputArray[1] = $splitr[0];
                            $input1 = implode(' ', $inputArray); // to cater for the reversed search, combine the array the other way
                        
            //Loop through the array of results gotten and extract the following: TITLE,SNIPPET AND IMAGE
                            for($x = 0; $x < $arrlength; $x++) {
                                $title=strtolower($resultArray[$x]['title']);
            
                                $snippet=strtolower($resultArray[$x]['snippet']);

                               //Counting the occurence of the search term in the title and the snippet
                                $titleCount = (substr_count($title, $search)); //counting the occurence of the input in the title
                                
                                if ($titleCount==0)
                                    $titleCount = (substr_count($title, $input1)); //counting the occurence of the reversed input in the title
                                    
                                $snippetCount =(substr_count($snippet, $search)); //counting the occurence of the input in the snippet
                                    if ($snippetCount==0)
                                    $snippetCount = (substr_count($snippet, $input1)); //counting the occurence of the reversed input in the snippet
                                    

                                //to get the exact searching terms
                                if (((preg_match("~\b$input1\b~",$title))||(preg_match("~\b$search\b~",$title)))&&($snippetCount!=0))
                                //if (((strpos($input1,$title))||(strpos($search,$title)))&&($snippetCount!=0))
                                {
                               
                
                                    $snippetBody = $resultArray[$x]['snippet'];
                                    if  (strpos($title, '·')) {
                                        $newTit= explode(' · ', $title);
                                        array_push($titleArray1, $newTit[1]);
                                        }
                                        elseif  (strpos($title, ' | ')) {
                                        $newTit= explode(' | ', $title);
                                        array_push($titleArray1, $newTit[1]);
                                        }
                                        elseif  (strpos($title, '-')) {
                                            $newTit= explode(' - ', $title);
                                            array_push($titleArray1, $newTit[1]) ;
                                        }elseif  (strpos($title, '•')) {
                                            $newTit= explode(' • ', $title);
                                            array_push($titleArray1, $newTit[1]) ;
                                        }

                                      
                                   // $jsonArrayNew['profile'][$x] =$snippetBody;

                                    $snipppet = array_key_exists('snippet', $resultArray[$x]);
                                    if ($snipppet){
                                        $profile = $resultArray[$x]['snippet'];
                                        //array_push($occupationArray, $occupation);
                                        $jsonArrayNew1[$x] = $profile;
                                      
                                    } else{
                                   
                                        $jsonArrayNew1[$x]= NULL;
                                        
                                    }


                                    $hcards = array_key_exists('hcard', $resultArray[$x]['pagemap']);
                                    if ($hcards){
                                        $occupation = $resultArray[$x]['pagemap']['hcard'][0]['title'];
                                        //array_push($occupationArray, $occupation);
                                        $jsonArrayNew2[$x] = $occupation;
                                        
                                        // $photoUrl = $resultArray[$x]['pagemap']['hcard'][0]['photo'];
                                        // array_push($urlImgArray, $photoUrl);
                                    } else{
                                    // array_push($occupationArray, 'Not availabe');
                                        $jsonArrayNew2[$x]= NULL;
                                        
                                    }
                                    
                                    $metatag = array_key_exists('og:title', $resultArray[$x]['pagemap']['metatags'][0]);
                                    if ($metatag){
                                        $description = $resultArray[$x]['pagemap']['metatags'][0]['og:description'];
                                        $jsonArrayNew3[$x] = $description;
                                        
                                        //array_push($descriptionArray, $description);
                                    }else{
                                    // array_push($descriptionArray, 'Not availabe');
                                    $jsonArrayNew3[$x] = NULL;
                                    
                                    }

                                    $img_url = array_key_exists('cse_image', $resultArray[$x]['pagemap']);
                                    if ($img_url){
                                        $img_src =  $resultArray[$x]['pagemap']['cse_image'][0]['src'];
                                        //array_push($imageArray, $img_src);
                                        $jsonArrayNew4[$x] = $img_src;
                                    
                                    }else{
                                    // array_push($imageArray, 'Not availabe');
                                        $jsonArrayNew4[$x] =  NULL;
                                        
                                    }
                                  

                                }
                                

                                $titleCount1 = (substr_count($title, $splitr[0]));
                                $titleCount2 = (substr_count($title, $splitr[1])); 
                                $snippetCount1 =(substr_count($snippet, $splitr[0]));
                                $snippetCount2 =(substr_count($snippet, $splitr[1]));
                                

                            if (($titleCount!=0)&&($snippetCount!=0)){
                                $sum = $sum + 1.00;
                                }elseif(($titleCount==0)&&($snippetCount!=0)){
                                    $sum =$sum + 0.30;
                                }elseif(($titleCount!=0)&&($snippetCount==0)){
                                    $sum =$sum + 0.70;
                                }elseif (($titleCount1!=0)&&($snippetCount1!=0)){
                                    $sum = $sum + 0.50;
                                }elseif(($titleCount1==0)&&($snippetCount1!=0)){
                                    $sum =$sum + 0.15;
                                }elseif(($titleCount1!=0)&&($snippetCount1==0)){
                                    $sum =$sum + 0.35;
                                }elseif (($titleCount2!=0)&&($snippetCount2!=0)){
                                    $sum = $sum + 0.50;
                                }elseif(($titleCount2==0)&&($snippetCount2!=0)){
                                    $sum =$sum + 0.15;
                                }elseif(($titleCount2!=0)&&($snippetCount2==0)){
                                    $sum =$sum + 0.35;
                                }elseif (($titleCount1==0)&&($snippetCount1==0)){
                                    $sum = $sum + 0.00;
                                }elseif (($titleCount2==0)&&($snippetCount2==0)){
                                    $sum = $sum + 0.00;
                                }elseif (($titleCount==0)&&($snippetCount==0)){
                                    $sum = $sum + 0.00;
                                }
                        }
                       
                
            //     foreach($jsonArrayNew1 as $key=> $value){
            //         if ($jsonArrayNew1[$key]!=NULL){
            //    $arraycombined[$key] = $jsonArrayNew1[$key];
              
               
            //         }else {
            //             $arraycombined[$key] = NULL;
            //         }
                
            //     } 

   
            $merged = array_map(null,$jsonArrayNew1,$jsonArrayNew2,$jsonArrayNew3,$jsonArrayNew4,$titleArray1);
           
            $collection = collect(['profile', 'occupation', 'description','img_src', 'source']);

            $combined = $collection->combine([$jsonArrayNew1, $jsonArrayNew2, $jsonArrayNew3,$jsonArrayNew4,$titleArray1]);
            
           print_r($combined->all());
           
            $divisorCounter = count($jsonArrayNew2);
            var_dump($divisorCounter);
            // for ($i=0; $i<$divisorCounter; $i++){
            //     var_dump($jsonArrayNew1[$i]);
            //     // if ($jsonArrayNew1[$i]!=NULL){
            //     //        $arraycombined[$i] = $jsonArrayNew1[$i];
                      
                       
            //     //             }else {
            //     //                 $arraycombined[$i] = NULL;
            //     //             }
            // }
           
           var_dump($divisorCounter);
        
                
                        $divisorCounter = count($titleArray);
                      //  if ($divisorCounter<=3){
             //if the return array is less than or equal to 3
            
             //foreach( $titleArray as $title1 => $source ) {

              // print_r( array_map(null,$jsonArrayNew1,$jsonArrayNew2,$jsonArrayNew3,$jsonArrayNew4));
                $divisorCounter1 = count($titleArray1);
                if ($divisorCounter1==0){
                    return json_encode([
                        'error'=> 'Sorry, we could not find the name. Kindly, check for other names'
                    ]);
               }
              return json_encode([
                  'status' => 200,
                  'search_name' => ucwords($search),
                 'Number of people' => $divisorCounter1,
                  //'source' => $titleArray1,
                  'result' => $merged,
                //   'profile' =>$snippetArray,
                //   'likely_occupation' => $occupationArray,
                //   'description' => $descriptionArray,
                //   'img_src' => $imageArray,
                  'percentage' => ($sum*10)/2 . '%'
              ]); 
               
      
                    } else{
                        //if it is a single word search
                        $arrlength = count($resultArray);
                        for($x = 0; $x < $arrlength; $x++) {
                            $title=strtolower($resultArray[$x]['title']);
                            $snippet=strtolower($resultArray[$x]['snippet']);
                            $snippetCount =(substr_count($snippet, $search));

                            
                        
                            $titleCount = (substr_count($title, $search)); //counting the occurence of the title
                         
                            //to get the exact searching terms
                            if ((preg_match("~\b$search\b~",$title))&&($snippetCount!=0)){
                                //$imageUrl =  $resultArray[$x]['pagemap']['cse_image'][0]['src'];
                                $snippetBody = $resultArray[$x]['snippet'];
                                if  (strpos($title, '·')) {
                                    $newTit= explode(' · ', $title);
                                    array_push($titleArray1, $newTit[1]);
                                    }elseif  (strpos($title, '|')) {
                                    $newTit= explode(' | ', $title);
                                    array_push($titleArray1, $newTit[1]);
                                    }elseif  (strpos($title, '-')) {
                                        $newTit= explode(' - ', $title);
                                        array_push($titleArray1, $newTit[1]) ;
                                    }elseif  (strpos($title, '•')) {
                                        $newTit= explode(' • ', $title);
                                        array_push($titleArray1, $newTit[1]) ;
                                    }
                                
                                //array_push($snippetArray, $snippetBody);
                                $jsonArrayNew['profile'][$x] =$snippetBody;
                                $hcards = array_key_exists('hcard', $resultArray[$x]['pagemap']);
                            if ($hcards){
                                $occupation = $resultArray[$x]['pagemap']['hcard'][0]['title'];
                                //array_push($occupationArray, $occupation);
                                $jsonArrayNew['likely_occupation'][$x] = $occupation;
                                
                                // $photoUrl = $resultArray[$x]['pagemap']['hcard'][0]['photo'];
                                // array_push($urlImgArray, $photoUrl);
                               } else{
                               // array_push($occupationArray, 'Not availabe');
                                $jsonArrayNew['likely_occupation'][$x]= 'Not availabe';
                                
                               }
                             
                               $metatag = array_key_exists('og:title', $resultArray[$x]['pagemap']['metatags'][0]);
                              if ($metatag){
                                  $description = $resultArray[$x]['pagemap']['metatags'][0]['og:description'];
                                  $jsonArrayNew['description'][$x] = $description;
                                
                                  //array_push($descriptionArray, $description);
                              }else{
                               // array_push($descriptionArray, 'Not availabe');
                               $jsonArrayNew['description'][$x] = 'Not availabe';
                               
                               }

                               $img_url = array_key_exists('cse_image', $resultArray[$x]['pagemap']);
                              if ($img_url){
                                $img_src =  $resultArray[$x]['pagemap']['cse_image'][0]['src'];
                                //array_push($imageArray, $img_src);
                                $jsonArrayNew['img_src'][$x] = $img_src;
                            
                              }else{
                               // array_push($imageArray, 'Not availabe');
                                $jsonArrayNew['img_src'][$x] =  'Not availabe';
                                
                               }
                            
                            }
                            
                   
                            if (($titleCount!=0)&&($snippetCount!=0)){
                                $sum = $sum + 1.00;
                                }elseif(($titleCount==0)&&($snippetCount!=0)){
                                    $sum =$sum + 0.30;
                                }elseif(($titleCount!=0)&&($snippetCount==0)){
                                    $sum =$sum + 0.70;
                                }elseif (($titleCount==0)&&($snippetCount==0)){
                                    $sum = $sum + 0.00;
                                }
                            
                        }
                        $divisorCounter1 = count($titleArray1);
                            if ($divisorCounter1==0){
                                return json_encode([
                                    'error'=> 'Sorry, we could not find the name. Kindly, check for other names'
                                ]);
                        }
                        return json_encode([
                            'status' => 200,
                            'search_name' => ucwords($search),
                           'Number of people' => $divisorCounter1,
                           'result'=>$jsonArrayNew,
                            // 'source' => $titleArray1,
                            // 'profile' =>$snippetArray,
                            // 'likely_occupation' => $occupationArray,
                            // 'description' => $descriptionArray,
                            // 'img_src' => $imageArray,
                            'percentage' => ($sum*10)/$divisorCounter1 
                        ]); 
                         
                    }
                }
            }
        }

    }
    
}

