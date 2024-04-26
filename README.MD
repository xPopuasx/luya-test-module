### Установка
 ```sh 
  composer require sitis/feature-test-module
 ```

### Настройка модуля (подключение)
 ```sh 
  'tests' => sitis\tests\admin\Module::class,
 ```

### Далее необходимо опубликовать тест файлы
 ```sh 
  ./luya tests/tests/vendor-publish 
 ```

### Дальше необходимо настроить конфигурацию yml tests/api.suite.yml
 ```sh 
  modules:
    enabled:
        - REST:
              depends: PhpBrowser
              url: 'http://maxim-demidov.loc/api' -- необходимо указать свой домен
              part: [ json ]
 ```

### Так же опубликовался файл который будет считываться  tests_resources/data.json
 ```sh 
    "1": {
      "action": "catalog/live-search", - адрес
      "comment": "...", - коментарий что бы понимать какой кейс
      "method": "GET"  - коментарий метод,
      "responseJson": true - ожидает jsom,
      "status": "200" - какой статус ждём на выходе,
      "params": { - параметры запроса
        "query": "12"
      }
    }
 ```