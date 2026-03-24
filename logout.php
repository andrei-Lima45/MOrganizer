<?php
/**
 * ARQUIVO: logout.php
 * DESCRIÇÃO: Página de logout/saída do sistema
 * FUNCIONALIDADE:
 *   1. Inicia sessão
 *   2. Destrói toda a sessão ($_SESSION['logged'], $_SESSION['user'], etc)
 *   3. Redireciona para login
 * SEGURANÇA: Remove todos os dados da sessão para evitar remo acesso
 */

// Inicia sessão
session_start();

// Destrói toda a sessão (remove todas as variáveis de sessão)
// Isso invalida o acesso do usuário ao sistema
session_destroy();

// Redireciona para página de login
header('Location: login.php');

// Interrompe execução
exit;
