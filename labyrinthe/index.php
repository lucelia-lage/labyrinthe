<!-- ETAPES POUR BIEN REUSSIR LE LABYRINTHE : -->

<!-- Créer une grille à la main -->
<!-- Afficher la grille -->
<!-- Remplir la grille du chat, des murs et de la souris -->
<!-- Afficher les boutons de direction -->
<!-- Gérer les déplacements du chat -->
<!-- a) déplacer le chat dans une case vide -->
<!-- b) déplacer le chat dans un mur -->
<!-- c) déplacer le chat en dehors des limites de la grille -->
<!-- d) garder en mémoire la position du chat dans la grille -->
<!-- Gérer la victoire -->
<!-- Afficher et rendre effectif le bouton "Recommencer" -->
<!-- Appliquer le brouillard -->
<!-- Ajouter un autre labyrinthe choisi aléatoirement au lancement -->
<?php
session_start();

$grid = [ // M = mur; C = chat; S = souris; MA = marteau
    [
        ["M", "M", "M", "MA", "M", "S"],
        ["M", "C", " ", " ", "M", "M"],
        ["M", " ", "M", " ", " ", "M"],
        ["M", " ", " ", "M", " ", "M"],
        ["M", " ", "M", " ", " ", "M"],
    ],
    [
        ["M", "M", "M", "M", "M", "M"],
        ["M", "C", "", "MA", "M", "S"],
        ["M", " ", " ", " ", " ", "M"],
        ["M", " ", "M", " ", " ", "M"],
        ["M", " ", "M", " ", " ", "M"],
    ]
];

// initialiser le labyrinthe : 
if (!isset($_SESSION['grid']) || isset($_POST['reset'])) { // si grid n'est pas défini ou si reset est dans l'url alors... 
    $_SESSION['grid'] = $grid[array_rand($grid)]; // un labyrinthe au hasard
    $_SESSION['cat_x'] = 1; // position initiale du chat (horizontal)
    $_SESSION['cat_y'] = 1; // position initiale du chat (vertical)
    $_SESSION['victory'] = false; // réinitialiser l'état de victoire à false
    $_SESSION['score'] = 10; // réinitialiser le score à 10
    $_SESSION['lost'] = false; // réinitialiser la défaite
    $_SESSION['canbreak'] = false; // le chat ne peut pas encore voir et utiliser le marteau
}

// récup les variables de la session (persistence des données ? ) : 
$grid = $_SESSION['grid']; // structure du labyrinthe
$cat_x = $_SESSION['cat_x']; // position actuelle du chat (ligne)
$cat_y = $_SESSION['cat_y']; // position actuelle du chat (colonne)
$rows = count($grid); // nmbr de lignes
$cols = count($grid[0]); // nmbr de colonnes

// gérer les déplacements : 
if (isset($_POST['move'])) { // move est présente dans post ? alors... 
    $directionX = 0; // direction axe horizontal
    $directionY = 0; // axe vertical 
    switch ($_POST['move']) {
        case 'up':
            $directionX = -1;
            break;
        case 'down':
            $directionX = 1;
            break;
        case 'left':
            $directionY = -1;
            break;
        case 'right':
            $directionY = 1;
            break;
    }
    $newPosition_x = $cat_x + $directionX;
    $newPosition_y = $cat_y + $directionY;

    // les limites et obstacles : 
    if ($newPosition_x >= 0 && $newPosition_x < $rows && $newPosition_y >= 0 && $newPosition_y < $cols) {
        $way = $grid[$newPosition_x][$newPosition_y];

        if ($way === "M") {
            if ($_SESSION["canbreak"]) {
                $_SESSION["grid"][$newPosition_x][$newPosition_y] = " ";
                $_SESSION["canbreak"] = false;
            } else {
                // - de points à chaque mur q le chat se prend
                $_SESSION['score']--;
                if ($_SESSION['score'] <= 0) {
                    $_SESSION['lost'] = true;
                }
            }
        } else {
            // déplacer le chat...
            $_SESSION['cat_x'] = $newPosition_x;
            $_SESSION['cat_y'] = $newPosition_y;
            $cat_x = $newPosition_x;
            $cat_y = $newPosition_y;
            // trouve la souris ?
            if ($way === "S") {
                $_SESSION['victory'] = true;
            }
            // prend le marteau ?
            if ($way === "MA") {
                $_SESSION["canbreak"] = true;
                $_SESSION["grid"][$newPosition_x][$newPosition_y] = " "; // vider la grille
            }
        }
    }
}
// messages de victoire et de défaite : 
$victory_message = $_SESSION['victory'] ? "<h2>Bon appétit, petit chat !</h2>" : "";
$lost_message = $_SESSION['lost'] ? "<h2>Oupsiii, dommage, c'est la défaite, mon petit chat !</h2>" : "";
?>

<!DOCTYPE html>
<html>

<head>
    <title>Labyrinthe du Chat</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>

    <div class="gridsContainer">

        <h1>Coucou le petit chat... vas-y chercher ton "amie", la souris ! </h1>
        <table class="grid">

            <?php

            for ($i = 0; $i < $rows; $i++) {  // parcourir chaque ligne du labyrinthe
                echo "<tr>";
                for ($j = 0; $j < $cols; $j++) {  // parcourir chaque colonne de la ligne 
                    $image = "brouillard.png"; // brouillard par défaut 

                    // la case est dans le "champ de vision" du chat (autour du chat dans un rayon de 1)
                    if (($cat_x - $i) * ($cat_x - $i) <= 1 && ($cat_y - $j) * ($cat_y - $j) <= 1) {
                        //différence des coordonnées est inférieure ou égale à 1 => case est visible autour du chat
                        if ($grid[$i][$j] === 'M') { // case est un mur ? on définit l'image du mur
                            $image = "mur.png";
                        } else if ($i === $cat_x && $j === $cat_y) { // case est un chat ?  on définit l'image du chat
                            $image = "chat.png";
                        } else if ($grid[$i][$j] === 'S') { // case est la souris ? on définit l'image de la souris
                            $image = "souris.png";
                        } else if ($grid[$i][$j] === 'MA') { // case est la marteau ? on définit l'image de la marteau
                            $image = "marteau.png";
                        } else {
                            $image = ""; //  case est vide? on ne définit aucune image
                        }
                    }
                    echo "<td>" . ($image ? "<img src='images/$image' alt=''>" : "") . "</td>"; // montre l'image dans une cellule de tableau
                }
                echo "</tr>";  // fin de la ligne dans le tableau HTML
            }
            ?>
        </table>
    </div>

    <!-- form de déplacement :  -->
    <div class="buttonsContainer">

        <div class="messages">
            <?= $victory_message ?>
            <?= $lost_message ?>
            <?= "Score" . " " . $_SESSION['score'] . " - " . "ATTENTION: Si tu te prends un mur, tu perds des points ici ! Sois malin petit chat ! " ?>
            <p>Marteau: <?= $_SESSION["canbreak"] ? "Bravo! Casse ce mur et va chercher ta petite souris! " : "Si tu croises un marteau, tu peux casser des murs! " ?></p>
        </div>

        <form method="POST">
            <p><button name="move" value="up">↑</button></p>
            <button name="move" value="left">←</button> <button name="move" value="right">→</button>
            <p><button name="move" value="down">↓</button></p>
        </form>

        <!-- btn pour réinitialiser :  -->
        <form method="POST">
            <button name="reset" value="true">Recommencer</button>
        </form>

    </div>
</body>

</html>