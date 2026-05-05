# Contao License Bundle

Zentrales Lizenz-Bundle für kommerzielle Contao-Erweiterungen von Erdmann & Freunde.
Verwaltet Lizenzschlüssel, prüft Major-Version-Bindung und schaltet Pro-Features frei.

## Konzept

- **Major-Bindung**: Ein Schlüssel gilt für eine Major-Version (z.B. v1.x). Upgrades auf eine neue Major (v2.x) erfordern einen neuen Schlüssel.
- **Trial**: Trial-Schlüssel sind normale Schlüssel mit `state=trial` und Ablaufdatum. Sie werden manuell vom Maintainer erzeugt.
- **Graceful degradation**: Ist der Lizenzserver nicht erreichbar, gilt eine Karenzzeit von 7 Tagen.

## Integration in ein Pro-Bundle

In der `config/services.yaml` des kommerziellen Bundles:

```yaml
services:
  ErdmannFreunde\ContaoMailAutomationBundle\License\Registration:
    class: ErdmannFreunde\ContaoLicenseBundle\License\ProductRegistration
    arguments:
      $productKey: 'mail-automation'
      $productName: 'Contao Mail Automation Pro'
      $currentMajor: 1
      $vendorUrl: 'https://erdmann-freunde.de/mail-automation'
    tags:
      - { name: 'erdmannfreunde.license.product' }
```

### Programmatischer Check im Pro-Code

```php
public function __construct(private LicenseManager $licenses) {}

public function renderProSlider(): string
{
    if (!$this->licenses->isValid('mail-automation')) {
        return $this->renderUpgradeNotice();
    }
    return $this->renderActualSlider();
}
```

### Deklarativer Check über Attribute

Auf Controllern oder Action-Methoden:

```php
use ErdmannFreunde\ContaoLicenseBundle\Attribute\RequiresLicense;

#[RequiresLicense('mail-automation')]
class ProEditorController
{
    #[Route('/contao/pro-editor/save', methods: ['POST'])]
    public function save(): Response { /* ... */ }
}
```

Das `LicenseCheckListener` wertet das Attribute auf `kernel.controller`-Events aus und antwortet bei fehlender oder ungültiger Lizenz mit HTTP 403 plus klarer Fehlermeldung. Trials lassen sich per `#[RequiresLicense('mail-automation', allowTrial: false)]` ausschließen.

## Backend

Im Contao-Backend erscheint unter _System → Lizenzen_ eine tabellarische Übersicht aller registrierten Produkte mit Spalten **Produkt · Lizenzschlüssel · Status · Gültig bis · Letzte Prüfung**. Pro Produkt wird ein Schlüssel hinterlegt; der Status wird vom Lizenzserver synchronisiert und gecacht (24 h).

## CLI

```
vendor/bin/contao-console erdmannfreunde:license:check
```

Prüft alle registrierten Produkte gegen den Server (Cache wird verworfen).
