<?php

/**
 * CÓDIGO ORIGINAL (LEGACY) - Solo como referencia.
 *
 * Problemas identificados:
 * 1. SQL Injection: concatenación directa de variables en la query.
 * 2. Credenciales hardcodeadas en el código fuente.
 * 3. Sin validación de datos de entrada (tipos, formatos, valores vacíos).
 * 4. Sin manejo de errores: solo retorna "OK" o "ERROR" sin contexto.
 * 5. Sin orientación a objetos: función procedural suelta.
 * 6. Sin cierre de conexión a base de datos (resource leak).
 * 7. Duración por defecto como string ('30') en lugar de int.
 * 8. Sin separación de responsabilidades (conexión, validación, persistencia).
 *
 * Ver legacy/src/ para la versión refactorizada.
 */

// function crearCita($datos)
// {
//     $db = mysqli_connect('localhost', 'root', '', 'clinica');
//     if (!$datos['paciente_id'] || !$datos['dentista_id']) return 'ERROR';
//     $inicio = $datos['fecha'] . ' ' . $datos['hora'];
//     $fin = date('Y-m-d H:i:s', strtotime($inicio) + ($datos['duracion'] ?: '30') * 60);
//     $sql = "INSERT INTO citas (paciente_id, dentista_id, inicio, fin, motivo) VALUES (" .
//         $datos['paciente_id'] . "," . $datos['dentista_id'] . ",'" . $inicio . "','" . $fin . "','" . $datos['motivo'] . "')";
//     mysqli_query($db, $sql);
//     $id = mysqli_insert_id($db);
//
//     return $id ? "OK" : "ERROR";
// }
