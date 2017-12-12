# Kochbuch

## Einleitung

## Treiber

Jede Platform wird durch einen Treiber angesprochen und  diese implementieren verschiedene Interfaces und stellen damit einen verschieden großen Funktionsumfang bereit.
Bisher unterstüzt das Data-Source Element folgende Platformen.


### PostgreSQL

* Base
* Manageble
* Routable
* Geographic

### Oracle

* Base
* Geographic

### SQLite

* Base

### YAML

* Base


## Interfaces

Interfaces definieren einen festen  Funktionsumfang, der vom dem implementiereden Treiber erfüllt werden muss. Folgende Interfaces exisieren momentan:

### Base

Das BaseInterface stellt grundlegenden CRUD Funktionen zur Verfügung. Hierbei kann es aber je nach Datenquelle zu Einschränkungen kommen.

* **getById:** Sucht in der Datenquelle anhand gegebener Id
* **create:** Erstellt ein DataItem basierend auf einem gegebenen Array und der definierten Strukturder Datenquelle
* **save:** Speichert gegebenes DataItem in der Datenquelle.
* **remove:** Löscht gegebenes DataItem in der Datenquelle.
* **connect:** Stellt eine Verbindung mit der Platform her.
* **isReady:**
* **canRead:** Ermittelt ob Leserechte vorliegen.
* **canWrite:** Ermittelt ob Schreibrechte vorliegen.
* **getPlatformName:** Gibt den Platformnamen zurück.
* **search:** Sucht in der Datenquelle anhand gegebener Filterbedingung.


### Geographic

* **addGeometryColumn:**
* **getTableGeomType:** Selektiert den Geometrietyo der Geometriespalte
* **transformEwkt:** Transformiert ein eWKT in eine gegebene Projektion.
* **getIntersectCondition:** Generiert ein SQL-Statement, um zu überprüfen, ob Geometire a und b sich schneiden
* **getGeomAttributeAsWkt:** ???
* **findGeometryFieldSrid:** Gibt die SRID der Geometriespalte zurück.

### Managable

Wenn ein Treiber das Interface Managable unterstüzt, kann mit diesem die Datenquelle verwaltet werden. Dazu stehen folgende Funktionen zur Verfügung.

* **listDatabases:** Listet alle Datenbanken der Datenquelle auf.
* **listSchemas:**  Listet alle Schemas der Datenquelle auf.
* **listTables:**   Listet alle Tabellen der Datenquelle auf.
* **createTable:** Erzeugt eine Tabelle in der Datenquelle
* **dropTable:**  Löscht eine Tabelle in der Datenquelle
* **getLastInsertId:** Gibt die zu letzt eingefügte Id zurück



### Routable

Wenn ein Treiber das Interface Routable implementiert, kann mit diesem Routing durchgeführt werden


* **getNodeFromGeom:** Ermittelt anhand einer gegebenen Geometrie den nächsten Knoten auf dem Graphen.
* **routeBetweenNodes:** Ermittelt eine Route zwischen zwei gegebenen Knoten.

## FeatureType

**???**

## Beispiele


### Konfiguration PostgresSQL

Ohne Geodaten

```
dataStoreConnection:
    connection: default
    table: fom_user
    uniqueId: id
    fields: [username, email]
```

Mit Geodaten

### Initiialsierung eines DataStores

```
$configuration = $this->getConfiguration();
$dataStoreConfig = $configuration["dataStores"]['myDataStore'];
$dataStore = new DataStore($this->container, $dataStoreConfig);
```

### Suchen von bestimmten Geometrien  die sich mit BBox schneiden

```

$results = [];


$uID = $dataStoreConfig["uniqueId"];

$whereCondition = $uID. " LIKE '".$searchId."%'";

$dataItems = $dataStore->search(['where'=> $whereCondition ]);



```


