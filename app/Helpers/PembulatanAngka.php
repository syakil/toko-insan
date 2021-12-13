<?php

function roundUpToAny($n,$x=5) {
    return round(($n+$x/2)/$x)*$x;
}

?>