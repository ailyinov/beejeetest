<?php

$routeDispatcher = FastRoute\simpleDispatcher(function(FastRoute\RouteCollector $r) {
    $r->addRoute('GET', '/[page/{page:\d+}]', ['\BeeJee\Controller\TaskController', 'tasksList']);
    $r->addRoute(['GET', 'POST'], '/edit/{task_id:\d+}', ['\BeeJee\Controller\TaskController', 'edit', ['edit']]);
    $r->addRoute(['GET', 'POST'], '/auth', ['\BeeJee\Controller\AuthController', 'logIn']);
    $r->addRoute('GET', '/log-out', ['\BeeJee\Controller\AuthController', 'logOut']);
    $r->addRoute(['GET', 'POST'], '/add', ['\BeeJee\Controller\TaskController', 'add']);
});
