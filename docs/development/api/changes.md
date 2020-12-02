# API Changes

## 2.x vs 1.x

farmOS 1.x used the [RESTful Web Services](https://drupal.org/project/restws)
module, which provided API endpoints for each entity type (asset, log, taxonomy
term, etc).

farmOS 2.x uses the new JSON:API module included with Drupal core, which
follows the [JSON:API](https://jsonapi.org/) specification for defining API
resources.

The root API endpoint is `/api`.

### JSON Schema

farmOS 2.x also provides [JSON Schema](https://json-schema.org/) information
about all available resources. The root endpoint for schema information is
`/api/schema`.

In farmOS 1.x, the `/farm.json` endpoint provided similar information in the
`resources` property. This has been removed in favor of JSON Schema.

### Farm info endpoint

In farmOS 1.x, an informational API endpoint was provided at `/farm.json`. This
included various information describing the farmOS server configuration,
authenticated user, installed languages and available entity types and bundles.
This information was provided as either a simple value or a JSON object:

```json
{
  "name": "My Farm",
  "url": "https://myfarm.mydomain.com",
  "api_version": "1.0",
  "system_of_measurement": "metric",
  "user": { ... },
  "languages": { ... },
  "resources": { ... },
  "metrics": { ... }
}
```

In farmOS 2.x, a root `/api` endpoint either provides this information, or is a
gateway to this information.

The simple values previously available from
`/farm.json` are now provided in the `meta.farm` object at `/api`:

```json
{
   "jsonapi":{ ... },
   "data":[],
   "meta":{
      "links":{
         "me":{
            "meta":{
               "id":"7b2af019-3191-40ca-b221-616f9a365722"
            },
            "href":"http://localhost/api/user/user/7b2af019-3191-40ca-b221-616f9a365722"
         }
      },
      "farm":{
         "name":"My farm name",
         "url":"http://localhost",
         "version":"2.x",
         "system_of_measurement": "metric"
      }
   },
   "links":{ ... }
}
```

The `resources` object has been replaced with the `links` object that
describes all the available resource types and their endpoints. Information
previously provided in the other JSON objects are now available as standalone
resources at their respective endpoints:

- `user` - `/api/user/user`
    - The authenticated user's ID is included in the `meta.links.me` object
      with a link to the user's resource. The user's attributes, such as name
      and language, can be retrieved from that endpoint.
- `languages` -  `/api/configurable_language/configurable_language`

### Resource endpoints

In farmOS 1.x, API endpoints for each entity type were available at
`/[entity_type].json`.

For example: `/log.json`

In farmOS 2.x, a root `/api` endpoint is provided, with a `links` object that
describes all the available resource types and their endpoints. These follow
a URL pattern of `/api/[entity-type]/[bundle]`.

For example: `/api/log/activity`

"Bundles" are "sub-types" that can have different sets (bundles) of fields on
them. For example, a "Seeding Log" and a "Harvest Log" will collect different
information, but both are "Logs" (events).

To illustrate the difference between 1.x and 2.x, here are the endpoints for
retrieving all Activity logs.

- farmOS 1.x: `/log.json?type=farm_activity`
- farmOS 2.x: `/api/log/activity`

### IDs

farmOS 2.x assigns
[UUIDs](https://en.wikipedia.org/wiki/Universally_unique_identifier)
(universally unique identifiers) to all resources, and uses them in the API.

This differs from farmOS 1.x, which used the integer IDs directly from the
auto-incrementing database table that the record was pulled from. The benefit
of UUIDs is they are guaranteed to be unique across multiple farmOS databases,
whereas the old IDs were not.

The internal integer IDs are not exposed via the API, so all code that needs to
integrate should use the new UUIDs instead.

Also note that the migration from farmOS 1.x to 2.x does not preserve the
internal integer IDs, so they may be different after migrating to 2.x.

### Record structure

JSON:API has some rules about how records are structured that differ from
farmOS 1.x. These rules make the API more explicit.

In farmOS 1.x, all the fields/properties of a record were on the same level.

For example, a simple observation log looked like this:

```
{
    "id": "5"
    "type": "farm_observation",
    "name": "Test observation",
    "timestamp": "1526584271",
    "asset": [
      {
        "resource": "farm_asset",
        "id": "123"
      }
    ]
}
```

In farmOS 2.x, JSON:API dictates that the "attributes" and "relationships" of a
record be explicitly declared under `attributes` and `relationships` properties
in the JSON.

The same record in farmOS 2.x looks like:

```
{
  "id": "9bc49ffd-76e8-4f86-b811-b721cb771327"
  "type": "log--observation",
  "attributes": {
    "name": "Test observation",
    "timestamp": "1526584271",
  },
  "relationships": {
    "asset": {
      "data": [
        {
          "type": "asset--animal",
          "id": "75116e3e-c45e-431d-8b58-1fce6bb315cf",
        }
      ]
    }
  }
}
```

### Filtering

The URL query parameters for filtering results have a different syntax in 2.x.
Refer to the [Drupal.org JSON:API Filtering documentation](https://www.drupal.org/docs/core-modules-and-themes/core-modules/jsonapi-module/filtering)
for more information.

To illustrate, this is how to filter activity logs by their completed status:

- farmOS 1.x: `/log.json?type=activity&done=1`
- farmOS 2.x: `/api/log/activity?filter[status]=complete`

### Text format

Long text fields (like `notes`) include `value` and `format` sub-properties,
where `value` is the text value, and `format` is the "Text format" to use when
displaying the text. This is used to filter user-supplied text, to only allow
certain HTML tags (filtering out potential XSS vulnerabilities), convert URLs
to links, etc.

This works the same in farmOS 2.x, but the default `format` has changed from
`farm_format` to `default`.

### Logs

#### Log types

The `farm_` prefix has been dropped from all log type names. For example, in
farmOS 1.x an Activity log was `farm_activity`, and in farmOS 2.x it is simply
`activity`.

Additionally, the "Soil test" and "Water test" log types have been merged into
a single "Lab test" log type.

Below is the full list of log types in farmOS 1.x and their new names in 2.x:

- `farm_activity` -> `activity`
- `farm_harvest` -> `harvest`
- `farm_input` -> `input`
- `farm_maintenance` -> `maintenance`
- `farm_medical` -> `medical`
- `farm_observation` -> `observation`
- `farm_purchase` -> `purchase`
- `farm_sale` -> `sale`
- `farm_seeding` -> `seeding`
- `farm_soil_test` -> `lab_test`
- `farm_transplanting` -> `transplanting`
- `farm_water_test` -> `lab_test`

#### Log fields

Log field names are largely unchanged, with a few exceptions (note that *new*
fields are not listed here):

- `date_purchase` -> `purchase_date`
- `done` -> `status` (see "Log status" below)
- `files` -> `file`
- `flags` -> `flag`
- `geofield` -> `geometry`
- `images` -> `image`
- `input_method` -> `method`
- `input_source` -> `source`
- `log_category` -> `category`
- `log_owner` -> `owner`
- `seed_source` -> `source`
- `soil_lab` -> `lab`
- `water_lab` -> `lab`

See also "Text format" above for information about the changes to the `format`
parameter of long text fields.

#### Log status

In farmOS 1.x, logs had a boolean property called `done` which was either `1`
(done) or `0` (not done).

In 2.x, the `done` property has changed to `status`, and can be set to either
`done` or `pending`. Additional states may be added in the future.

### Assets

Asset records in farmOS 1.x had an entity type of `farm_asset`. In farmOS 2.x,
the `farm_` prefix has been dropped. The entity type is now simply `asset`.

#### Asset types

Asset type names are largely unchanged, with one notable exception: the
"Planting" asset type has been renamed to "Plant".

Below is the full list of asset types in farmOS 1.x and their new names in 2.x:

- `animal` (unchanged)
- `compost` (unchanged)
- `equipment` (unchanged)
- `group` (unchanged)
- `planting` -> `plant`
- `sensor` (unchanged)

#### Asset fields

Asset field names are largely unchanged, with a few exceptions (note that *new*
fields are not listed here):

- `archived` -> `status` and `archived` (see "Asset status" below)
- `date` -> `birthdate` (on `animal` assets)
- `description` -> `notes` (see also "Text format" above)
- `flags` -> `flag`
- `files` -> `file`
- `images` -> `image`
- `animal_castrated` -> `castrated`
- `animal_nicknames` -> `nickname`
- `animal_sex` -> `sex`
- `animal_tag` -> `id_tag`

#### Asset status

In farmOS 1.x, assets had a property called `archived` which was either `0`,
which indicated that the asset was active, or a timestamp that recorded when
the asset was archived.

In farmOS 2.x, these have been split into two separate fields:

- `status` - The status of the asset (either `active` or `archived`).
- `archived` - The timestamp when the asset was archived. This will be empty
  if the asset is active.

### Taxonomies

farmOS 2.x continues to use Drupal's core `taxonomy_term` entities to represent
vocabularies of terms. The vocabulary machine names have changed, to drop the
`farm_` prefix, and to standardize plurality.

- `farm_log_categories` -> `log_category`
- `farm_materials` -> `material`
- `farm_season` -> `season`

### Quantities

In farmOS 1.x, log quantities were saved within separate field collection
entities. farmOS used the RESTful Web Services Field Collection module to
hide the fact that these were separate entities, allowing their field to be
accessed and modified in the same request to the host entity.

In farmOS 2.x, log quantities are separate `farm_quantity` entities. This means
that they are a separate JSONAPI resource `farm_quantity--farm-quantity` and are
included in the `relationships` property on `logs`. This means that
adding quantities to a new or existing log, they must be created in a
separate API request before they can be referenced by the log. Quantities
still have `measure`, `value`, `unit` and `label` fields.
