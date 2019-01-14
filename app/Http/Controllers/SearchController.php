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
        $profiles=[];
        $occupations=[];
        $descriptions=[];
        $img_srcs=[];
        $sum = [];
        
        

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
                                $sum[$x] = 0;
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
                                    $sum[$x] = $sum[$x] + 30;
                               
                
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

                                      
                        

                                    $snipppet = array_key_exists('snippet', $resultArray[$x]);
                                    if ($snipppet){
                                        $profile = $resultArray[$x]['snippet'];
                                        //array_push($occupationArray, $occupation);
                                        $profiles[$x] = $profile;
                                        $sum[$x] = $sum[$x] + 10;
                                      
                                    } else{
                                   
                                        $profiles[$x]= NULL;
                                        
                                    }


                                    // $hcards = array_key_exists('hcard', $resultArray[$x]['pagemap']);
                                    // if ($hcards){
                                    //     $occupation = $resultArray[$x]['pagemap']['hcard'][0]['title'];
                                    //     //array_push($occupationArray, $occupation);
                                    //     $occupations[$x] = $occupation;
                                        
                                    //     // $photoUrl = $resultArray[$x]['pagemap']['hcard'][0]['photo'];
                                    //     // array_push($urlImgArray, $photoUrl);
                                    // } else{
                                    // // array_push($occupationArray, 'Not availabe');
                                    //     $occupations[$x]= NULL;
                                        
                                    // }
                                    
                                    // $metatag = array_key_exists('og:title', $resultArray[$x]['pagemap']['metatags'][0]);
                                    // if ($metatag){
                                    //     $description = $resultArray[$x]['pagemap']['metatags'][0]['og:description'];
                                    //     $descriptions[$x] = $description;
                                        
                                    //     //array_push($descriptionArray, $description);
                                    // }else{
                                    // // array_push($descriptionArray, 'Not availabe');
                                    // $descriptions[$x] = NULL;
                                    
                                    // }

                                    // $img_url = array_key_exists('cse_image', $resultArray[$x]['pagemap']);
                                    // if ($img_url){
                                    //     $img_src =  $resultArray[$x]['pagemap']['cse_image'][0]['src'];
                                    //     //array_push($imageArray, $img_src);
                                    //     $img_srcs[$x] = $img_src;
                                    
                                    // }else{
                                    // // array_push($imageArray, 'Not availabe');
                                    //     $img_srcs[$x] =  NULL;
                                        
                                    // }
                                  
                                    $hcards = array_key_exists('hcard', $resultArray[$x]['pagemap']);

                                    //checking if there is linkedin details to get likely occupation
                                    if ($hcards){
                                    $hcardss = array_key_exists('title',  $resultArray[$x]['pagemap']['hcard'][0]);
                                    $sum[$x] = $sum[$x] + 10;
                                    if ($hcardss){
                                        $occupation = $resultArray[$x]['pagemap']['hcard'][0]['title'];
                                       
                                        $occupations[$x] = $occupation;
                                        $sum[$x] = $sum[$x] + 20;
                                        } else{
                                            $occupations[$x]= NULL;
                                        }
                                    
                                   } else{
                                   
                                    $occupations[$x]= NULL;
                                    
                                   }
                                 
                                   //checking if there is facebook details to get likely description
                              
                                      $metatags = array_key_exists('og:description',  $resultArray[$x]['pagemap']['metatags'][0]);
                                    //   $sum[$x] = $sum[$x] + 10;
                                      if ($metatags){
                                            $description = $resultArray[$x]['pagemap']['metatags'][0]['og:description'];
                                            
                                            $descriptions[$x] = $description;
                                            $sum[$x] = $sum[$x] + 20;
                                        } else{
                                            $descriptions[$x]= NULL;
                                            //$sum[$x] = $sum[$x] - 20;
                                        }
                                   
                                   //Pushing image URLs to an array
                                   $img_url = array_key_exists('cse_image', $resultArray[$x]['pagemap']);
                                  if ($img_url){
                                    $img_src =  $resultArray[$x]['pagemap']['cse_image'][0]['src'];
                                    
                                    $img_srcs[$x] = $img_src;
                                    $sum[$x] = $sum[$x] + 10;
                                  }else{
                                
                                    $img_srcs[$x] = NULL;
                                    
                                   }
                                }
                                

                            //     $titleCount1 = (substr_count($title, $splitr[0]));
                            //     $titleCount2 = (substr_count($title, $splitr[1])); 
                            //     $snippetCount1 =(substr_count($snippet, $splitr[0]));
                            //     $snippetCount2 =(substr_count($snippet, $splitr[1]));
                                

                            // if (($titleCount!=0)&&($snippetCount!=0)){
                            //     $sum = $sum + 1.00;
                            //     }elseif(($titleCount==0)&&($snippetCount!=0)){
                            //         $sum =$sum + 0.30;
                            //     }elseif(($titleCount!=0)&&($snippetCount==0)){
                            //         $sum =$sum + 0.70;
                            //     }elseif (($titleCount1!=0)&&($snippetCount1!=0)){
                            //         $sum = $sum + 0.50;
                            //     }elseif(($titleCount1==0)&&($snippetCount1!=0)){
                            //         $sum =$sum + 0.15;
                            //     }elseif(($titleCount1!=0)&&($snippetCount1==0)){
                            //         $sum =$sum + 0.35;
                            //     }elseif (($titleCount2!=0)&&($snippetCount2!=0)){
                            //         $sum = $sum + 0.50;
                            //     }elseif(($titleCount2==0)&&($snippetCount2!=0)){
                            //         $sum =$sum + 0.15;
                            //     }elseif(($titleCount2!=0)&&($snippetCount2==0)){
                            //         $sum =$sum + 0.35;
                            //     }elseif (($titleCount1==0)&&($snippetCount1==0)){
                            //         $sum = $sum + 0.00;
                            //     }elseif (($titleCount2==0)&&($snippetCount2==0)){
                            //         $sum = $sum + 0.00;
                            //     }elseif (($titleCount==0)&&($snippetCount==0)){
                            //         $sum = $sum + 0.00;
                            //     }
                        }

   
            $merged = array_map(null,$profiles,$occupations,$descriptions,$img_srcs,$titleArray1,$sum);
           
        //     $collection = collect(['profile', 'occupation', 'description','img_src', 'source']);

        //     $combined = $collection->combine([$jsonArrayNew1, $jsonArrayNew2, $jsonArrayNew3,$jsonArrayNew4,$titleArray1]);
            
        //   print_r($combined->all());
           
          
  
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
                    'results' => $merged,
              ]); 
               
      
                    } else{
                        //if it is a single word search
                        $arrlength = count($resultArray);
                        for($x = 0; $x < $arrlength; $x++) {
                            $title=strtolower($resultArray[$x]['title']);
                            $snippet=strtolower($resultArray[$x]['snippet']);
                            $snippetCount =(substr_count($snippet, $search));
                            $titleCount = (substr_count($title, $search)); //counting the occurence of the title
                            $sum[$x] = 0;
                            //to get the exact searching terms in the title and the snippet
                            if ((preg_match("~\b$search\b~",$title))&&($snippetCount!=0)){
                                $sum[$x] = $sum[$x] + 30;
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
                              
                                $snipppet = array_key_exists('snippet', $resultArray[$x]);
                                    if ($snipppet){
                                        $profile = $resultArray[$x]['snippet'];
                                        
                                        $profiles[$x] = $profile;
                                        //$sum[$x] = $sum[$x] + 10;
                                      
                                    } else{
                                   
                                        $profiles[$x]= NULL;
                                        
                                    }
                                $hcards = array_key_exists('hcard', $resultArray[$x]['pagemap']);

                                //checking if there is linkedin details to get likely occupation
                            if ($hcards){
                                $hcardss = array_key_exists('title',  $resultArray[$x]['pagemap']['hcard'][0]);
                                $sum[$x] = $sum[$x] + 10;
                                if ($hcardss){
                                    $occupation = $resultArray[$x]['pagemap']['hcard'][0]['title'];
                                   
                                    $occupations[$x] = $occupation;
                                   // $sum[$x] = $sum[$x] + 20;
                                    } else{
                                        $occupations[$x]= NULL;
                                    }
                                
                               } else{
                               
                                $occupations[$x]= NULL;
                                
                               }
                             
                               //checking if there is facebook details to get likely description
                          
                                  $metatags = array_key_exists('og:description',  $resultArray[$x]['pagemap']['metatags'][0]);
                                  $sum[$x] = $sum[$x] + 10;
                                  if ($metatags){
                                        $description = $resultArray[$x]['pagemap']['metatags'][0]['og:description'];
                                        
                                        $descriptions[$x] = $description;
                                       // $sum[$x] = $sum[$x] + 20;
                                    } else{
                                        $descriptions[$x]= NULL;
                                        //$sum[$x] = $sum[$x] - 20;
                                    }
                               
                               //Pushing image URLs to an array
                               $img_url = array_key_exists('cse_image', $resultArray[$x]['pagemap']);
                              if ($img_url){
                                $img_src =  $resultArray[$x]['pagemap']['cse_image'][0]['src'];
                                
                                $img_srcs[$x] = $img_src;
                               // $sum[$x] = $sum[$x] + 10;
                              }else{
                            
                                $img_srcs[$x] = NULL;
                                
                               }
                                if (( $img_srcs[$x] != NULL)&& ( $occupations[$x] != NULL)&&($profiles[$x] != NULL)&&($descriptions[$x]!= NULL)){
                                    $sum[$x] = $sum[$x] + 70;
                                }elseif (( $img_srcs[$x] != NULL)&& ( $occupations[$x] != NULL)&&($profiles[$x] != NULL)&&($descriptions[$x]= NULL)){
                                    $sum[$x] = $sum[$x] + 60;
                                }elseif(( $img_srcs[$x] != NULL)&& ( $occupations[$x] != NULL)&&($profiles[$x] = NULL)&&($descriptions[$x]!= NULL)){
                                    $sum[$x] = $sum[$x] + 50;
                                }elseif(( $img_srcs[$x] != NULL)&& ( $occupations[$x] = NULL)&&($profiles[$x] != NULL)&&($descriptions[$x]!= NULL)){
                                    $sum[$x] = $sum[$x] + 50;
                                }elseif(( $img_srcs[$x] = NULL)&& ( $occupations[$x] != NULL)&&($profiles[$x] != NULL)&&($descriptions[$x]!= NULL)){
                                    $sum[$x] = $sum[$x] + 50;
                                }elseif(( $img_srcs[$x] != NULL)&& ( $occupations[$x] != NULL)&&($profiles[$x]== NULL)&&($descriptions[$x]== NULL)){
                                    $sum[$x] = $sum[$x] + 40;
                                }elseif(( $img_srcs[$x] != NULL)&& ( $occupations[$x] == NULL)&&($profiles[$x]!= NULL)&&($descriptions[$x]== NULL)){
                                    $sum[$x] = $sum[$x] + 40;
                                }elseif(( $img_srcs[$x] == NULL)&& ( $occupations[$x] != NULL)&&($profiles[$x] != NULL)&&($descriptions[$x]== NULL)){
                                    $sum[$x] = $sum[$x] + 40;
                                }elseif(( $img_srcs[$x] != NULL)&& ( $occupations[$x] == NULL)&&($profiles[$x] == NULL)&&($descriptions[$x]!= NULL)){
                                    $sum[$x] = $sum[$x] + 30;
                                }elseif(( $img_srcs[$x] == NULL)&& ( $occupations[$x] != NULL)&&($profiles[$x] == NULL)&&($descriptions[$x]!= NULL)){
                                    $sum[$x] = $sum[$x] + 30;
                                }elseif(( $img_srcs[$x] == NULL)&& ( $occupations[$x] == NULL)&&($profiles[$x] != NULL)&&($descriptions[$x]!= NULL)){
                                    $sum[$x] = $sum[$x] + 30;
                                } elseif(( $img_srcs[$x] == NULL)&& ( $occupations[$x] == NULL)&&($profiles[$x] == NULL)&&($descriptions[$x]!= NULL)){
                                    $sum[$x] = $sum[$x] + 10;
                                }elseif(( $img_srcs[$x] == NULL)&& ( $occupations[$x] == NULL)&&($profiles[$x] != NULL)&&($descriptions[$x]==NULL)){
                                    $sum[$x] = $sum[$x] + 20;
                                }elseif(( $img_srcs[$x] == NULL)&& ( $occupations[$x] != NULL)&&($profiles[$x] == NULL)&&($descriptions[$x]== NULL)){
                                    $sum[$x] = $sum[$x] + 20;
                                }elseif(( $img_srcs[$x] != NULL)&& ( $occupations[$x] == NULL)&&($profiles[$x] == NULL)&&($descriptions[$x]== NULL)){
                                    $sum[$x] = $sum[$x] + 20;
                                }elseif(( $img_srcs[$x] == NULL)&& ( $occupations[$x] == NULL)&&($profiles[$x] == NULL)&&($descriptions[$x]== NULL)){
                                    $sum[$x] = $sum[$x] + 00;
                                }
                            }
                            
                            
                           
                        }
                        $divisorCounter1 = count($titleArray1);
                            if ($divisorCounter1==0){
                                return json_encode([
                                    'error'=> 'Sorry, we could not find the name. Kindly, check for other names'
                                ]);
                        }
                        //combining all the results array in an array for mapping
                         $merged = array_map(null,$profiles,$occupations,$descriptions,$img_srcs,$titleArray1,$sum);
                        return json_encode([
                            'status' => 200,
                            'search_name' => ucwords($search),
                            'Number of people' => $divisorCounter1,
                            'results'=>$merged,
                        
                        ]); 
                         
                    }
                }
            }
        }

    }
    
}

