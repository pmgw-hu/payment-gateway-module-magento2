# PaymentGateway for Magento 2

## Magento 2.x Community Edition telepítése

* Letöltés

    `https://magento.com/tech-resources/download`
    
    Ingyenes regisztráció szükséges. A teszt adatokat is töltsük le, egyszerűbb lesz az életünk.
    
### Telepítés

- Csinájlunk egy könyvtárat a projektünkek, pl: `magento` néven, a letöltött Magentot másoljuk be ebbe könyvrárába, a sample fájlos cuccot is, ha külön töltöttük le

- start `traefik`

- `docker-compose build`

- `docker-compose up -d`

- Ezután két féle módon folytathatod a telepítést: webes warázslóval vagy parancssorból:

    #### Webes telepítés
    
    - browserben: https://magento.dev.big.hu/setup
    
    - a telepítés menete értelemszerű, de ha elakadnál: http://devdocs.magento.com/guides/v2.2/install-gde/install/web/install-web.html
            
    #### Parancssoros telepítés
        
    - `docker exec -it magento_web_1 /bin/bash` <br>(itt a magento_web_1 függ a docker imagéről, amelyet a könyvtár nevőből alkot)
    
    - `cd var/www/dev/magento/`
    
    - `php bin/magento setup:install --db-host=magento-db --db-name=magento --db-user=test --db-password=test --admin-firstname=FISH --admin-lastname=BIG --admin-email=<email_cim> --admin-user=bfadmin --admin-password=Nagyhal123 --use-secure=1 --base-url-secure=https://magento.dev.big.hu/ --use-secure-admin=1 --backend-frontname=admin --use-rewrites=1 --admin-use-security-key=0`<br>
    (az --admin-email=\<email_cim\> opcióba valamilyen saját email címet írjunk)
    
    - Ha hiba nélkül lefut a telepítés, az utolsó sorokban valahol lesz egy ilyen:<br>
    `[SUCCESS]: Magento Admin URI: /admin`<br>
    Tehát az admin felületet a majd /admin alatt érjük el.

- Nyissuk meg szövegszerkesztőben az app/etc/env.php fájlt, és módosítsuk az alábbi sorokat:<br>
`array (
    'save' => 'files',
  ),`
<br>erre:<br>
`array (
    'save' => 'memcached',
    'save_path' => 'magento-memcache:11211',
  ),`

- Böngészőben nézzük meg az eredményt: <br>
  https://magento.dev.big.hu/

- Relax, sokáig tart az első indulás (is)...

- Lépjünk be az adminba is: <br>
  https://magento.dev.big.hu/admin

- Adminban a System / Cache Managementben gyorsan kapcsoljuk be az összes cache-t, mielőtt kihullik az összes hajunk. Utána ráérünk majd egyenként beállítgatni és ki-be kapcsolgatni...            
    
## BIG FISH PaymentGateway for Magento 2 telepítése

- Regisztrád magad a https://marketplace.magento.com/ oldalon.

- Regisztráció után a My Account / My Access Keys alatt lesz egy public és egy private key, ezeket írd be az auth.json fájlba (auth.json.sample szerint).

- `composer require bigfish/paymentgateway`<br>
(ez csak addig szükséges, amíg nincs publikálva a modul a magestore-ban)

- `php bin/magento setup:upgrade`

- `php bin/magento setup:di:compile`<br>
Ez akár 10-20 percig is futhat, ne add fel!

- Admin / Stores / Configuration / Advanced / System / Full Page Cache:
    - Caching Applicaton: `Varnish cache`
    - Varnish Configuration:
        - Access list: `localhost`
        - Backend host: `magento-varnish`
        - Backend port: `8080`

- És végül a lényeg: Admin / Stores / Configuration / Sales / Payment Methods
Itt ha mindenki úgy akarja megjelenik a **BIG FISH Payment Gateway Settings**

- Hell yeah!

## Fejlesztés

- Legyen mindig egy docker terminál nyitva, gyorsabb magento parancsokat kiadni mint a webes adminban keresgélni:<br>
`docker exec -it magento_web_1 /bin/bash`<br>
Magento parancslista:<br>
`php bin/magento`

- URN séma generálása xml fájlokhoz, PHPStorm számára:
`php bin/magento dev:urn-catalog:generate .idea/misc.xml`

- A dependency injectiont elvileg minden osztály konstruktor módosítás után újra kéne fordítani a `php bin/magento setup:di:compile` paranccsal,
de mivel ez 10-20 percig is eltarthat, jobb ha developer módoba váltunk:<br>
`php bin/magento deploy:mode:set developer`<br>
Ezután elég csak a ./var/generation (magento 2.1x) vagy ./generated (magento 2.2x) könyvtárakat üríteni:<br>
`rm -R var/generation/*`<br>
vagy<br>
`rm -R generated/*`
   

## Gyakran előforduló problémák

- "There has been an error processing your request" üzenet
Error log record number: nnnnnnn
A hiba részleteit megtalálod a var/report/nnnnnnn fájlokban.

- Warning: SessionHandler::read()...
Ha csak néha fordul elő, egy-két page refresh segít. A hibát az egymásra csúszó aszinkron ajax hívások és a sessionök összeakadása okozza.

