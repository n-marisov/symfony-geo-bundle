## Пакет добавляет возможность хранения географических координат и фигур в базе данных.

### Подключение пакета.

```php
    # config/bundles.php

    return [
        # ...
        Maris\Symfony\Geo\GeoBundle::class => ["all" => true]
    ];
```

### Добавление таблиц в БД.

```yaml
    # config/packages/doctrine.yaml

    orm:
      #...
      mappings:
        #...
        GeoBundle:
          is_bundle: true
          type: 'xml'

        App:
          # ....
```

### После необходимо совершить миграцию doctrine. 

#### В базу данных добавится две таблицы.
    
- `сoordinates` : В таблице хранятся все координаты.
- `geometries` : В таблице хранятся все фигуры (линии, полигоны).

### Или можно использовать GeoJsonType

```yaml
    # config/packages/doctrine.yaml
  dbal:
    types:
        geo_json: Maris\Symfony\Geo\Types\GeoJsonType
```

#### Данные хранятся в виде json (GeoJson) строки и приводятся к типу Location, Polyline или Polygon.

### Настройка пакета.
#### Создайте файл config/packages/geo.yaml

```yaml
    # config/packages/geo.yaml
    geo:
      # Стандарт эллипсоида для расчетов.
      ellipsoid: WGS_66

      # Допустимая погрешность при сравнениях объектов в метрах
      allowed: 0.15

      # Количество знаков после запятой при кодировании полилиний в строку
      precision: 6
```

#### По умолчанию сервис GeoCalculator использует SphericalCalculator он более стабильный, хоть и менее точный.
#### SphericalCalculator предполагает что планета имеет форму сферы.
#### EllipsoidalCalculator предполагает что планета имеет форму эллипсоида.
#### Что бы это переопределить устанавливаем в файле сервиса проекта.

```yaml
    # config/services.yaml

    services:
      # Устанавливаем калькулятор.
      Maris\Symfony\Geo\Service\GeoCalculator: '@Maris\Symfony\Geo\Service\EllipsoidalCalculator'
```
