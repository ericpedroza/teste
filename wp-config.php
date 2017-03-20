<?php
/**
 * As configurações básicas do WordPress
 *
 * O script de criação wp-config.php usa esse arquivo durante a instalação.
 * Você não precisa user o site, você pode copiar este arquivo
 * para "wp-config.php" e preencher os valores.
 *
 * Este arquivo contém as seguintes configurações:
 *
 * * Configurações do MySQL
 * * Chaves secretas
 * * Prefixo do banco de dados
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/pt-br:Editando_wp-config.php
 *
 * @package WordPress
 */

// ** Configurações do MySQL - Você pode pegar estas informações
// com o serviço de hospedagem ** //
/** O nome do banco de dados do WordPress */
define('DB_NAME', 'site');

/** Usuário do banco de dados MySQL */
define('DB_USER', 'admin');

/** Senha do banco de dados MySQL */
define('DB_PASSWORD', 'admin123');

/** Nome do host do MySQL */
define('DB_HOST', 'localhost');

/** Charset do banco de dados a ser usado na criação das tabelas. */
define('DB_CHARSET', 'utf8mb4');

/** O tipo de Collate do banco de dados. Não altere isso se tiver dúvidas. */
define('DB_COLLATE', '');

/**#@+
 * Chaves únicas de autenticação e salts.
 *
 * Altere cada chave para um frase única!
 * Você pode gerá-las
 * usando o {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org
 * secret-key service}
 * Você pode alterá-las a qualquer momento para desvalidar quaisquer
 * cookies existentes. Isto irá forçar todos os
 * usuários a fazerem login novamente.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'wwJ8n;JP#eSgw9@huA-*jY]@Y{3&-VZ7CWAA$;<%MF^5IM6j/{bdM0JhWRI!v;4o');
define('SECURE_AUTH_KEY',  '8D;II+PzM+b/TLu7s!zd5cY1a{ZvrpYj$^fW4<F4*lcu`l5^|y<mT,i&giG9eNlp');
define('LOGGED_IN_KEY',    'Z[KZOefG?~zORCy-D+N`b!|;h!m2E!<~U1mOS+/=_nL=!8X#SOJ<Tf2>Qi%Gu%-4');
define('NONCE_KEY',        '61)w0Tm};SB6:a^_;~eKekl##+dz45FNueNBTNbQVlTn[t=Re+0c9 b-zfP}AilB');
define('AUTH_SALT',        'YHTa+rzE`./4G^4Ce57-g>L1Uw C/a Et*T)}AkfrAD*VCZB:Hk{bs}]psh=zI8*');
define('SECURE_AUTH_SALT', '|Y){O=dx!kxl,Mik[~-E^!^z(f?Y=D-.Ts/m+Q*L;i&QpQ=d?*lp?,E``j$[eRBk');
define('LOGGED_IN_SALT',   '*sBu7RJtJ.zEZSzLA67o/[(>DuP]eSk!:n<OF$k.8#JFXfzB}(^^X1K+):=$/-Fs');
define('NONCE_SALT',       'F*U||Ceqgm/fJy?ak%7,Q<JW$aEYs|X:=-#n@U+LHhm&zLG0&&Eq2oGN8q*aj}M1');

/**#@-*/

/**
 * Prefixo da tabela do banco de dados do WordPress.
 *
 * Você pode ter várias instalações em um único banco de dados se você der
 * para cada um um único prefixo. Somente números, letras e sublinhados!
 */
$table_prefix  = 'wp_';

/**
 * Para desenvolvedores: Modo debugging WordPress.
 *
 * Altere isto para true para ativar a exibição de avisos
 * durante o desenvolvimento. É altamente recomendável que os
 * desenvolvedores de plugins e temas usem o WP_DEBUG
 * em seus ambientes de desenvolvimento.
 *
 * Para informações sobre outras constantes que podem ser utilizadas
 * para depuração, visite o Codex.
 *
 * @link https://codex.wordpress.org/pt-br:Depura%C3%A7%C3%A3o_no_WordPress
 */
define('WP_DEBUG', false);

/* Isto é tudo, pode parar de editar! :) */

/** Caminho absoluto para o diretório WordPress. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Configura as variáveis e arquivos do WordPress. */
require_once(ABSPATH . 'wp-settings.php');
