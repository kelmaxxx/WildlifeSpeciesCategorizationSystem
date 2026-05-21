<?php
/**
 * One-time seed script. Drops + reinserts every collection so re-running is
 * safe. Use this when you don't have MySQL handy and just want a fully
 * populated MongoDB matching the original SQL dataset.
 *
 * Safety: a plain GET only shows a confirmation page. The actual drop +
 * reseed runs only when the user POSTs the confirm form. After running,
 * DELETE THIS FILE so it can't be re-triggered.
 */
require_once __DIR__ . '/mongo.php';

$confirmed = $_SERVER['REQUEST_METHOD'] === 'POST'
          && ($_POST['confirm'] ?? '') === 'YES_RESET_DB';

if (!$confirmed) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
      <?php $title='Seed database'; $css=['public']; include __DIR__ . '/partials/head.php'; ?>
    </head>
    <body>
    <div style="max-width:640px;margin:4rem auto;padding:2rem;background:#fff;border-radius:14px;box-shadow:0 4px 16px rgba(0,0,0,.08);font-family:'Google Sans',sans-serif">
      <h1 style="margin-top:0;color:#92400e">&#9888; Reset the wildlife database?</h1>
      <p style="color:#475569">
        This will <strong>drop every collection</strong> (users, categories, habitats,
        species, activity_log) and reinsert the demo dataset. Any custom data
        currently in the database will be lost.
      </p>
      <p style="color:#475569">
        After running you should delete <code>seed.php</code> so it can't be re-triggered.
      </p>
      <form method="POST" style="margin-top:1.5rem;display:flex;gap:.75rem">
        <input type="hidden" name="confirm" value="YES_RESET_DB">
        <a href="index.php" style="padding:.6rem 1rem;border-radius:8px;border:1px solid #cbd5e1;color:#334155;text-decoration:none;font-weight:600">Cancel</a>
        <button type="submit" style="padding:.6rem 1rem;border-radius:8px;border:none;background:#b91c1c;color:#fff;font-weight:600;cursor:pointer">
          Drop and reseed
        </button>
      </form>
    </div>
    </body>
    </html>
    <?php
    exit;
}

// ---------- Drop everything --------------------------------------------------
$manager = new MongoDB\Driver\Manager(MONGO_URI);
foreach (['users', 'categories', 'habitats', 'species', 'activity_log'] as $coll) {
    try {
        $manager->executeCommand(MONGO_DB, new MongoDB\Driver\Command(['drop' => $coll]));
    } catch (MongoDB\Driver\Exception\Exception $e) {
        // collection didn't exist — ok
    }
}

// ---------- Users ------------------------------------------------------------
$db->insert('users', [
    'username'   => 'admin',
    'password'   => password_hash('admin123', PASSWORD_DEFAULT),
    'role'       => 'admin',
    'created_at' => new MongoDB\BSON\UTCDateTime(),
]);
$db->insert('users', [
    'username'   => 'user_jane',
    'password'   => password_hash('password123', PASSWORD_DEFAULT),
    'role'       => 'user',
    'created_at' => new MongoDB\BSON\UTCDateTime(),
]);

// ---------- Categories -------------------------------------------------------
$catSeed = ['Carnivore', 'Herbivore', 'Omnivore'];
$catIds  = []; // sql id => Mongo ObjectId + name
foreach ($catSeed as $i => $name) {
    $oid = $db->insert('categories', ['name' => $name]);
    $catIds[$i + 1] = ['id' => $oid, 'name' => $name];
}

// ---------- Habitats ---------------------------------------------------------
$habSeed = [
    1  => ['Tropical Rainforest', 'Amazon, Congo, Southeast Asia'],
    2  => ['Savanna',             'African Plains, Australia'],
    3  => ['Desert',              'Sahara, Gobi, Arabian'],
    4  => ['Tundra',              'Arctic, Northern Canada, Alaska'],
    5  => ['Temperate Forest',    'Europe, Eastern USA, East Asia'],
    6  => ['Grassland',           'Prairies (USA), Pampas (Argentina)'],
    7  => ['Wetlands',            'Everglades (USA), Pantanal (Brazil)'],
    8  => ['Mountain',            'Himalayas, Andes, Rockies'],
    9  => ['Coastal',             'Mangroves, Coral Reefs, Beaches'],
    10 => ['Freshwater',          'Lakes, Rivers, Ponds'],
    11 => ['Marine/Ocean',        'Pacific Ocean, Coral Reefs'],
    12 => ['Mangrove Forest',     'Sundarbans, Southeast Asia'],
];
$habIds = []; // sql id => Mongo ObjectId + name + location
foreach ($habSeed as $sqlId => [$name, $loc]) {
    $oid = $db->insert('habitats', ['name' => $name, 'location' => $loc]);
    $habIds[$sqlId] = ['id' => $oid, 'name' => $name, 'location' => $loc];
}

// ---------- Species (from the original SQL dump, denormalized) ---------------
// Each row: [name, scientific_name, is_endangered, category_sql_id, habitat_sql_id]
$speciesSeed = [
    ['Philippine Tarsier',           'Carlito syrichta',           1, 3, 1],
    ['Tamaraw',                      'Bubalus mindorensis',        1, 2, 1],
    ['Philippine Eagle',             'Pithecophaga jefferyi',      1, 1, 1],
    ['Philippine Cobra',             'Naja philippinensis',        1, 1, 1],
    ['Philippine Deer',              'Rusa marianna',              1, 2, 1],
    ['Saltwater Crocodile',          'Crocodylus porosus',         1, 1, 9],
    ['Philippine pygmy woodpecker',  'Yungipicus maculatus',       0, 1, 5],
    ['Philippine bush warbler',      'Horornis seebohmi',          0, 1, 5],
    ['Bengal Tiger',                 'Panthera tigris tigris',     1, 1, 1],
    ['African Elephant',             'Loxodonta africana',         1, 1, 2],
    ['Giraffe',                      'Giraffa camelopardalis',     0, 1, 2],
    ['Dromedary Camel',              'Camelus dromedarius',        0, 1, 3],
    ['Polar Bear',                   'Ursus maritimus',            1, 1, 4],
    ['Grey Wolf',                    'Canis lupus',                0, 1, 5],
    ['Snow Leopard',                 'Panthera uncia',             1, 1, 8],
    ['Red Fox',                      'Vulpes vulpes',              0, 1, 5],
    ['Giant Panda',                  'Ailuropoda melanoleuca',     1, 1, 5],
    ['Lion',                         'Panthera leo',               1, 1, 2],
    ['Cheetah',                      'Acinonyx jubatus',           1, 1, 2],
    ['Jaguar',                       'Panthera onca',              1, 1, 1],
    ['American Bison',               'Bison bison',                0, 1, 6],
    ['Asian Elephant',               'Elephas maximus',            1, 1, 1],
    ['Koala',                        'Phascolarctos cinereus',     1, 1, 5],
    ['Wolverine',                    'Gulo gulo',                  1, 1, 4],
    ['Peregrine Falcon',             'Falco peregrinus',           0, 1, 8],
    ['Osprey',                       'Pandion haliaetus',          0, 1, 9],
    ['Sea Turtle',                   'Cheloniidae',                1, 1, 11],
    ['Emperor Penguin',              'Aptenodytes forsteri',       1, 1, 4],
    ['American Alligator',           'Alligator mississippiensis', 1, 1, 10],
    ['Bald Eagle',                   'Haliaeetus leucocephalus',   0, 1, 9],
    ['Blue Whale',                   'Balaenoptera musculus',      1, 1, 11],
    ['Great White Shark',            'Carcharodon carcharias',     1, 1, 11],
    ['Kangaroo',                     'Macropus rufus',             0, 1, 2],
    ['Wallaby',                      'Macropus',                   0, 1, 2],
    ['Emu',                          'Dromaius novaehollandiae',   0, 1, 2],
    ['Orangutan',                    'Pongo',                      1, 1, 1],
    ['Sumatran Rhino',               'Dicerorhinus sumatrensis',   1, 1, 1],
    ['Komodo Dragon',                'Varanus komodoensis',        1, 1, 9],
    ['Sloth',                        'Folivora',                   0, 1, 1],
    ['Pygmy Hippo',                  'Choeropsis liberiensis',     1, 1, 10],
    ['Red Kangaroo',                 'Macropus rufus',             0, 1, 2],
    ['Tasmanian Tiger',              'Thylacinus cynocephalus',    1, 1, 2],
    ['Mantis Shrimp',                'Stomatopoda',                0, 1, 11],
    ['Clownfish',                    'Amphiprioninae',             0, 1, 11],
    ['Moose',                        'Alces alces',                0, 1, 5],
    ['Albatross',                    'Diomedea',                   0, 1, 11],
    ['Bald Ibis',                    'Geronticus eremita',         0, 1, 9],
    ['Seagull',                      'Larus',                      0, 1, 9],
    ['Harpy Eagle',                  'Harpia harpyja',             1, 1, 1],
    ['Crocodile Monitor',            'Varanus salvadorii',         1, 1, 9],
];

$speciesCount = 0;
foreach ($speciesSeed as [$name, $sci, $endangered, $catSqlId, $habSqlId]) {
    $cat = $catIds[$catSqlId] ?? null;
    $hab = $habIds[$habSqlId] ?? null;
    $db->insert('species', [
        'name'             => $name,
        'scientific_name'  => $sci,
        'is_endangered'    => (bool) $endangered,
        'image_url'        => '',
        'category_id'      => $cat['id']   ?? null,
        'category_name'    => $cat['name'] ?? null,
        'habitat_id'       => $hab['id']       ?? null,
        'habitat_name'     => $hab['name']     ?? null,
        'habitat_location' => $hab['location'] ?? null,
        'uploader_id'      => null,
        'approval_status'  => 'approved',
        'created_at'       => new MongoDB\BSON\UTCDateTime(),
    ]);
    $speciesCount++;
}

// ---------- Done -------------------------------------------------------------
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php $title='Seed complete'; $css=['public']; include __DIR__ . '/partials/head.php'; ?>
</head>
<body>
<div style="max-width:640px;margin:4rem auto;padding:2rem;background:#fff;border-radius:14px;box-shadow:0 4px 16px rgba(0,0,0,.08);font-family:'Google Sans',sans-serif">
  <h1 style="margin-top:0;color:#14532d">&#10003; Seed complete</h1>
  <p style="color:#64748b">MongoDB has been populated with the original SQL dataset.</p>
  <table style="width:100%;border-collapse:collapse;margin:1rem 0">
    <tr><td style="padding:.5rem;border-bottom:1px solid #f1f5f9;font-weight:600">users</td>
        <td style="padding:.5rem;border-bottom:1px solid #f1f5f9">2 (admin / admin123, user_jane / password123)</td></tr>
    <tr><td style="padding:.5rem;border-bottom:1px solid #f1f5f9;font-weight:600">categories</td>
        <td style="padding:.5rem;border-bottom:1px solid #f1f5f9"><?= count($catSeed) ?></td></tr>
    <tr><td style="padding:.5rem;border-bottom:1px solid #f1f5f9;font-weight:600">habitats</td>
        <td style="padding:.5rem;border-bottom:1px solid #f1f5f9"><?= count($habSeed) ?></td></tr>
    <tr><td style="padding:.5rem;border-bottom:1px solid #f1f5f9;font-weight:600">species</td>
        <td style="padding:.5rem;border-bottom:1px solid #f1f5f9"><?= $speciesCount ?> (denormalized with category + habitat)</td></tr>
  </table>
  <div style="background:#fef3c7;border:1px solid #fde68a;border-radius:8px;padding:.75rem 1rem;color:#92400e;font-size:.9rem;margin:1rem 0">
    &#9888; <strong>Delete <code>seed.php</code> now</strong> so it can't be re-run accidentally.
  </div>
  <p>
    <a href="index.php" style="color:#1f7a3b;font-weight:600">&rarr; Visit the public site</a>
    &nbsp;·&nbsp;
    <a href="admin/login.php" style="color:#1f7a3b;font-weight:600">Admin login</a>
  </p>
</div>
</body>
</html>


