<?php

function gettoken() {
    $date = gmdate("Y-m-d\TH:i:s");
    $guid = str_pad(time(),32,"0",STR_PAD_LEFT);
    $key = "349fwoepqZwoq03FEDCSDQPQf024f034592495(#40123e02430#DAPasmcwEM";
    $hash = hash("SHA1", $date." ".$guid." ".$key);
    return $date." ".$guid." ".$hash;
}

function get_product_erp($sku) {
    $endpoint = "Articulos/Venta?IdCliente=15735&FechaDocumento=".date("Y-m-d")."&cantidadItemsPorPagina=1&numeroPagina=1&Codigo=";    
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => "http://190.210.180.235:23990/CordobaBebeSRL/".$endpoint.$sku,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 120,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => array(
            "CentumSuiteAccessToken: ".gettoken(),
            "Accept: application/json",
            "Content-Type: application/json"
        ),
    ));
    $response = json_decode(curl_exec($curl),true);
    $error = curl_error($curl);
    if ($error != "") { echo "Error curl1: ".$error; }
    sleep(1);
    $endpoint2 = "ArticulosSucursalesFisicas?stockComprometidoPorSucursalFisica=true&idsSucursalesFisicas=8361&codigoExacto=";
    $curl2 = curl_init();
    curl_setopt_array($curl2, array(
        CURLOPT_URL => "http://190.210.180.235:23990/CordobaBebeSRL/".$endpoint2.$sku,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 120,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => array(
            "CentumSuiteAccessToken: ".gettoken(),
            "Accept: application/json",
            "Content-Type: application/json"
        ),
    ));
    $response2 = json_decode(curl_exec($curl2),true);
    $error2 = curl_error($curl2);
    if ($error2 != "") { echo "Error curl2: ".$error2; }
    sleep(1);


    if (isset($response["Articulos"]["Items"][0])) {
        $Codigo = $response["Articulos"]["Items"][0]["Codigo"];
        $Precio = $response["Articulos"]["Items"][0]["Precio"];
        $ExistenciasTotal = $response2["Items"][0]["Existencias"];
        $result = array($sku, $Codigo, $Precio, $ExistenciasTotal);
    } else {
        return array(curl_error($curl2)." - ".curl_error($curl), false, $response, $response2);
    }

    return $result;
}
//var_dump(get_product_erp("BOTS-015"));
function update_product($id, $id_sku, $price, $stock) {
    $data["price"] = $price;
    $data["stock"] = $stock;

    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => "https://api.tiendanube.com/v1/808724/products/".$id."/variants/".$id_sku,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "PUT",
        CURLOPT_POSTFIELDS => json_encode($data), 
        CURLOPT_HTTPHEADER => array(
            "Authentication: bearer f65d278761a83bdf2e5887200f51f6373030ee39 ",
            "User-Agent: App IntegraciÃ³n ERP (ezequielcrosa@diezweb.com.ar)",
            "Content-Type: application/json"
        ),
    ));

    $response_tn= json_decode(curl_exec($curl),true);
    sleep(1);
    $err = curl_error($curl);
}


$curl = curl_init();
curl_setopt_array($curl, array(
    CURLOPT_URL => "https://api.tiendanube.com/v1/808724/products",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "GET",
    CURLOPT_HTTPHEADER => array(
        "Authentication: bearer f65d278761a83bdf2e5887200f51f6373030ee39 ",
        "User-Agent: App IntegraciÃ³n ERP (ezequielcrosa@diezweb.com.ar)",
        "Content-Type: application/json"
    ),
));

$response_tn= json_decode(curl_exec($curl),true);
$err = curl_error($curl);

$text_mail = "--------------------------------------------".date("Y-m-d H:i")."\n\r<br>";

echo "\n\r\n\r--------------------------------------------\n\r";
echo date("Y-m-d H:i")."\n\r";

for ($i=0; $i < count($response_tn); $i++) { 
    $id = $response_tn[$i]["id"];
    for ($f=0; $f < count($response_tn[$i]["variants"]); $f++) { 
        $sku = $response_tn[$i]["variants"][$f]["sku"];
        $id_sku = $response_tn[$i]["variants"][$f]["id"];
        
        $result_erp = get_product_erp($sku);
        sleep(1);

        if ($result_erp[3] != NULL && $result_erp[1] != NULL && $result_erp[1] != false) {
            update_product($id, $id_sku, $result_erp[2], $result_erp[3]);
            $text_mail .= "Se actualiza producto sku: ".$sku." con los siguientes datos:<br>Precio: ".$result_erp[2].", stock: ".$result_erp[3]."<br><br>";
            echo "Se actualiza producto sku: ".$sku.": (id, id_sku, result2, result3)\n\r";
            var_dump(array($id, $id_sku, $result_erp[2], $result_erp[3]));
            echo "\n\r";
        } else if ($result_erp[1] == false) {
            echo "No se obtienen datos en el ERP para el sku: ".$sku.". Detalle curl".$result_erp[0]."\n\r";
            var_dump($result_erp[2]);
            echo "\n\r";
            var_dump($result_erp[3]);
            $text_mail .= "No se encuentra el precio o el stock en el ERP para el sku: ".$sku."<br>";
        }
    }
}
$to = "ezequielcrosa@diezweb.com.ar,gastonmincoff@gmail.com";
$subject = "Resultado Proceso ERP-TiendaNube";

$headers = "MIME-Version: 1.0" . "\r\n";
$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";

mail($to,$subject,$text_mail,$headers);
?>
