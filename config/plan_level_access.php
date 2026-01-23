<?php

return [
    // Map plan IDs (not names) to level groups
    // Use plan IDs which are more stable than names
    1 => ['primary-lower', 'primary-upper', 'jhs'], // Essential plan ID
    2 => ['primary-lower', 'primary-upper', 'jhs', 'shs'], // Home School plan ID
    3 => ['primary-lower', 'primary-upper', 'jhs', 'shs', 'university'], // Extra Tuition plan ID
];