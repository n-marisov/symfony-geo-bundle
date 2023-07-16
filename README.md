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

