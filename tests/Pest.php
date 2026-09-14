<?php

use Tests\TestCase;

// Ta linia instruuje Pesta, aby do wszystkich testów w folderze Feature podpiął metody Laravela (np. getJson)
uses(TestCase::class)->in('Feature');
