<?php
declare(strict_types = 1);
function labels_map($cell_info) {
    if (!is_string($cell_info)) {
        if ($cell_info == 11) return '✅';
        if ($cell_info == 10) return '🎯';
        return '&#4448;';
    }
    switch ($cell_info) {
/*        case 0:
           return '🍞';
           break;
       case 1:
           return '🍗 ';
           break;
       case 2:
           return '🥓';
           break;
       case 3:
           return '🍟';
           break;
       case 4:
           return '🍕';
           break;
       case 5:
           return '🌮';
           break;
       case 6:
           return '🍔';
           break;
       case 7:
           return '🌭';
           break;
       case 8:
           return '🌯';
           break; */
        case 9:
            return '💣';
            break;
        case 10:
            return '🎯';
            break;
        case 33:
            return '💰';
            break;
        default:
            return (string)$cell_info;
            break;
    }
}

function labels_end_map($cell_info) {
    if (is_string($cell_info)) {
        return $label = '⛏';
    }
    switch ($cell_info) {
/*        case 0:
           return '🍞';
           break;
       case 1:
           return '🍗 ';
           break;
       case 2:
           return '🥓';
           break;
       case 3:
           return '🍟';
           break;
       case 4:
           return '🍕';
           break;
       case 5:
           return '🌮';
           break;
       case 6:
           return '🍔';
           break;
       case 7:
           return '🌭';
           break;
       case 8:
           return '🌯';
           break;
 */
        case 9:
            return '💣';
            break;
        case 10:
            return '🎯';
            break;
        case 33:
            return '💰';
            break;
        default:
            return (string)$cell_info;
            break;
    }
}
