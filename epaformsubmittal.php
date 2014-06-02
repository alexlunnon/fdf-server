<?php

//Step 1: Get the FDF Raw Data

$FDFData = file_get_contents('php://input');

//Check it to see if it is empty or too short

if ( strlen($FDFData)<10)

{
header("Location: http://www.pdfill.com/pdf_action.html#4");
exit;
}

//Step 2: Create a random number for file name

$newFileID = GetRandonFolerName();



//Step 3: New File Nameto save the FDF Data
$fdfFileName = substr($pdfFileName , 0, strlen($pdfFileName)-4); //remove .pdf
$fdfFileName = $newFileID;
$fdfFileName = "$fdfFileName.fdf"; //add .fdf
$fdffp = fopen($fdfFileName, "w");
fwrite($fdffp, $FDFData, strlen($FDFData)); //write into a file
fclose($fdffp);

//Step 4: Merge FDF with blank PDF form template
$fdf_file = $fdfFileName;
$pdf_file = "blankform.pdf";
$outpdf_file= $newFileID;

$command = escapeshellcmd("pdftk ".$pdf_file." fill_form ".$fdf_file."  output CompletedForms/".$outpdf_file.".pdf flatten");
system("PATH=\$PATH:/usr/local/bin/ && $command",$response);
if ($response===FALSE){
   //there was an error, handle it
}


//Step 5: Email Completed PDF form
$email_to = "your@email.com"; // The email you are sending to (example)
$email_from = "formprocessing@email.com"; // The email you are sending from (example)
$email_subject = "Successfully Submitted Form : SubmittedForm.pdf"; // The Subject of the email
$email_txt = "Your form has been successfully submitted. Your referance code is : ".$outpdf_file; // Message that the email has in it
$fileatt = "CompletedForms/".$outpdf_file.".pdf"; // Path to the file (example)
$fileatt_type = "application/pdf"; // File Type
$fileatt_name = "SubmittedForm.pdf"; // Filename that will be used for the file as the attachment
$file = fopen($fileatt,'rb');
$data = fread($file,filesize($fileatt));
fclose($file);
$semi_rand = md5(time());
$mime_boundary = "==Multipart_Boundary_x{$semi_rand}x";
$headers="From: $email_from"; // Who the email is from (example)
$headers .= "\nMIME-Version: 1.0\n" .
"Content-Type: multipart/mixed;\n" .
" boundary=\"{$mime_boundary}\"";
$email_message .= "This is a multi-part message in MIME format.\n\n" .
"--{$mime_boundary}\n" .
"Content-Type:text/html; charset=\"iso-8859-1\"\n" .
"Content-Transfer-Encoding: 7bit\n\n" . $email_txt;
$email_message .= "\n\n";
$data = chunk_split(base64_encode($data));
$email_message .= "--{$mime_boundary}\n" .
"Content-Type: {$fileatt_type};\n" .
" name=\"{$fileatt_name}\"\n" .
"Content-Transfer-Encoding: base64\n\n" .
$data . "\n\n" .
"--{$mime_boundary}--\n";

mail($email_to,$email_subject,$email_message,$headers);

//Send Success Response to PDF Reader as FDF Type and clean up FDF file
header('Content-type: application/vnd.fdf');
readfile('success.fdf');
unlink($fdf_file);

///////////////////////////////////

//Create a random file name

function GetRandonFolerName()

{

$newFileID = "";

for($i=0; $i<16; $i++)

{

$newFileID .= chr(65 + rand(0, 25) );

}

return $newFileID;

}

?>
