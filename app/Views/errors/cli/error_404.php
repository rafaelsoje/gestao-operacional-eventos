<?php

use CodeIgniter\CLI\CLI;

// Saída enxuta para 404 em contexto CLI (comandos internos/testes).
CLI::error('ERROR: ' . $code);
CLI::write($message);
CLI::newLine();
