<?php

if(isset($_GET['sequence'])){
	require_once __DIR__ . '\vendor\autoload.php';

	$sequence = $_GET['sequence'];

	try {
		$connection = new MongoDB\Client("mongodb://localhost:27017");
	} catch (Exception $e){
	    echo $e->getMessage();
		die(' Could not connect to database.');
    }

	$sequence = clearFibonacciSequence($sequence); //clearing sequence from gaps and comma symbols

    $sequence_array = explode(',', $sequence);

    $fibonacci_sequence_flag =  checkFibonacciSequence($sequence_array); //check sequence array for Fibonacci conditions

    if($fibonacci_sequence_flag){ //do reverse operation if the input sequence is a Fibonacci sequence
		$revers_sequence_array =  createReversArray($sequence_array); //create revers array  to the sequence array
		$revers_sequence = implode(',', $revers_sequence_array);
    } else {
		$revers_sequence_array = array();
		$revers_sequence = '';
    }

	$db = $connection->fibonacci_db;
	$table_sequence = $db->sequence;

	//write data into DB
	$table_sequence -> insertOne(
        [
          "input_sequence" => $sequence,
          "output_sequence" => $revers_sequence,
          "fibonacci_sequence" => $fibonacci_sequence_flag,
          "date" => date("d.m.Y H:i:s")
        ]
    );

	//work ----    $table_sequence -> insertOne(["input_sequence" => "AAAA"]);

    if(!$fibonacci_sequence_flag){
        echo "The sequence is not Fibonacci sequence";
    } else {
        echo "The sequence is a Fibonacci sequence <br> Revers sequence: " . $revers_sequence;
    }

} else {
	echo "Invalid sequence";
}

//clearing sequence from gaps and comma symbols
function clearFibonacciSequence($sequence){

    $sequence = preg_replace('/\s/', '', $sequence); //delete spaces from sequence

    trim($sequence, ','); //delete comma if it is as first or the end symbol

	return $sequence;

}

//check sequence array for Fibonacci conditions
function checkFibonacciSequence($sequence_array){

	$fibonacci_sequence_flag = true;
	$first_element = 0;
	$second_element = 0;

	if(count($sequence_array) < 3){
		return false; //fibonacci_sequence_flag = false
    }

	for  ($i=0; $i<count($sequence_array); $i++){

		if(!$fibonacci_sequence_flag){ //if sequence isn't Fibonacci - stop checking
			break;
		}

		if($i<2){ //checking first 2 elements for Fibonacci condition

			switch ($i){
				case 0:
					if($sequence_array[$i] != 1){
						$fibonacci_sequence_flag = false;
					} else {
						$first_element = (int)$sequence_array[$i];
					}
					break;

				case 1:
					if($sequence_array[$i] != 2){
						$fibonacci_sequence_flag = false;
					} else {
						$second_element = (int)$sequence_array[$i];
					}
					break;
			}

		} else { //checking element after 2 - need to be sum of 2 previous elements

			if($sequence_array[$i] != $first_element+$second_element){
				$fibonacci_sequence_flag = false;
				break;
			} else {
				$first_element = $second_element;
				$second_element = $sequence_array[$i];
			}

		}

	}

	if(!$fibonacci_sequence_flag){
		return false;
	} else {
		return true;
	}

}

//create revers array  to the sequence array
function createReversArray($sequence_array){

    $revers_array = array();
    $counter = 0;

	for  ($i=count($sequence_array)-1; $i>=0; $i--){
		$revers_array[$counter] = $sequence_array[$i];
		++$counter;
	}

	return $revers_array;

}

?>