<?php
require_once('init.php');
require_once('config.php');
require_once('session.php');
require_once('database.php');
require_once('auth.php');
require_once('template.php');

startSession();
checkUserIP();
authenticate();

outputStandardTemplateHeader('About');

echo '<a href="'.htmlentities(pathinfo($_SERVER['SCRIPT_NAME'])['dirname'] . '/').'">&lt; Back to main listing</a>
';

echo '<h1>About</h1>
<p>Garnet DeGelder\'s File Manager '.GD_FILEMANAGER_VERSION.'</p>
<h1>License</h1>
<p>Copyright (C) 2017  Garnet DeGelder</p>

<p>This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.</p>

<p>This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.</p>

<p>You should have received a copy of the GNU General Public License
along with this program.  If not, see &lt;<a href="http://www.gnu.org/licenses/">http://www.gnu.org/licenses/</a>&gt;.</p>
';

outputStandardTemplateFooter();
