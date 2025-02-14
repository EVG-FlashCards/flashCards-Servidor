<?php
    /*
        @author: Sergio Matamoros Delgado <smatamorosdelgado.guadalupe@alumnado.fundacionloyola.net>
        @license: GPL v3 2021
        @description: BackEnd del minijuego de flashcards. 
        Inicio principal de la administración de FlashCards
    */
    
    require __DIR__."/clases/procesos.php";
    require_once __DIR__ . "/estructura/maquetacion.php";

    //Versión 1 se elige un minijuego (desplegable)
    //Versión 2 se elige varios minijuegos en checkboxes

    session_start();
    //Comprobamos que tenga la sesión iniciada
    if(!isset($_SESSION["id"])) { header("Location: login.php"); }

    //Carga el HTML
    echo '<html>';
    head("Puntos");
    nav();
    main("puntuacion");
    echo '</html>';

    echo "<h2>¡Bienvenido, $_SESSION[userName]!</h2>";

    //FORMULARIO QUE PIDA LOS PUNTOS A INSERTAR
    if(isset($_POST["enviar"])) {

        $puntos = $_POST["iPuntos"];

        $bd = new Procesos();

        //Seleccionamos el top 10 (este se podría hacer con un * no es necesario el top.)
        $puntosOk = $bd->seleccionar("order by puntuacion desc limit 1");

        //Seleccionamos el top 10 de partidas, pero con la puntuación más baja
        $minPuntos = $bd->seleccionar("idPartida, puntuacion","WHERE puntuacion = (SELECT MIN(puntuacion) FROM partidas)");

        $filaPuntosMin = $bd->selectArray($minPuntos,MYSQLI_ASSOC);


        //Boolean que indica si has sido aceptado o no.
        $aceptado = false;
        //Mensaje personalizado para el usuario
        $mensaje = "";

        //Compruebo que si no hay 10 filas, añado el resultado directamente (y evito numeros negativos y 0).
        if($bd->num_Filas($puntosOk) < 10 && $puntos > 0) {
            echo '[debug, añado una nueva puntuación]';

            //Insertamos en la B.D los puntos nuevos.
            $bd->insertarDatos("INSERT INTO partidas(idUsuario, idMinijuego, puntuacion) VALUES (1,1,$puntos)");

            //Establecemos que el usuario ha entrado en el top10
            $aceptado = true;
        //
        } else if($puntos > $filaPuntosMin["puntuacion"]) {
            echo 'tus puntos son mayores que los de la bd<br>';

            
            //debug
            echo '[Debug] puntuaciones llena, actualizo.<br>';
            
            print_r($filaPuntosMin);

            echo "Puntuacion minima recogida: ". $filaPuntosMin["puntuacion"] . " con id: ". $filaPuntosMin["idPartida"];

            //Modifico los valores en la B.D.
            $bd->modificar($puntos, $filaPuntosMin["idPartida"]);

            //Establecemos que el usuario ha entrado en el top10
            $aceptado = true;

        } else {
            $aceptado = false;
        }
        echo ($aceptado) ? $mensaje = '<br>¡Tu puntuación ha entrado en el top 10!' : $mensaje = '<br>No has entrado en el top 10 :(';

        echo '<br>Tus puntos: '.$puntos;
    }
?>
