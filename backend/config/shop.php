<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Umbral de stock bajo
    |--------------------------------------------------------------------------
    |
    | Una variante se marca como "stock bajo" en el panel de administración
    | cuando su cantidad disponible es menor o igual a este número.
    |
    */

    'low_stock_threshold' => (int) env('LOW_STOCK_THRESHOLD', 5),

];
