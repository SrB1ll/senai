<?php
$senha_instrutor = 'instrutor123';
$senha_coped = 'coped123';

echo "Hash para instrutor: " . password_hash($senha_instrutor, PASSWORD_DEFAULT) . "\n";
echo "Hash para COPED: " . password_hash($senha_coped, PASSWORD_DEFAULT) . "\n";
?> 