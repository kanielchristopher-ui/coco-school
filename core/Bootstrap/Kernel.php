<?php
declare(strict_types=1);

namespace CocoCore\Bootstrap;

use CocoCore\Contracts\AuditLogInterface;
use CocoCore\Database\Connection;
use CocoCore\Database\Migrator;
use CocoCore\Database\PdoAuditLog;
use CocoCore\Database\PdoEnrollmentStore;
use CocoCore\Database\PdoFeeLedgerRepository;
use CocoCore\Database\PdoLicenseStore;
use CocoCore\Database\PdoPermissionRepository;
use CocoCore\Database\PdoUserRepository;
use CocoCore\Domain\Entity;
use CocoCore\Events\EventDispatcher;
use CocoCore\Licensing\LicenseManager;
use CocoCore\Licensing\LicenseVerifier;
use CocoCore\Modules\Enrollment\EnrollmentService;
use CocoCore\Repositories\EntityRepository;
use CocoCore\Modules\Auth\AuthService;
use CocoCore\Modules\Bulletins\BulletinWorkflow;
use CocoCore\Modules\Finance\PaymentService;
use CocoCore\Modules\Grading\ReportCardCalculator;
use CocoCore\Security\AuthorizationService;
use CocoCore\Security\PasswordHasher;

/**
 * Point d'entrée du Core : assemble les services. Aucune dépendance à une session, une vue ou un framework.
 *   $core = Kernel::boot(['host'=>..., 'name'=>..., 'user'=>..., 'pass'=>...]);
 *   $actor = $core->auth()->attempt('admin', 'secret');
 *   $core->payments()->collect($actor, 12, 25.0, 'cash');
 */
final class Kernel
{
    private EventDispatcher $events;
    private ?AuthorizationService $authorization = null;
    private ?LicenseManager $license = null;
    /** @var list<string>|null */
    private ?array $trustedKeys = null;

    public function __construct(private Connection $db)
    {
        $this->events = new EventDispatcher();
    }

    /** @param array{host:string,name:string,user:string,pass:string,port?:int} $dbConfig */
    public static function boot(array $dbConfig): self
    {
        return new self(Connection::fromConfig($dbConfig));
    }

    public function db(): Connection
    {
        return $this->db;
    }

    public function events(): EventDispatcher
    {
        return $this->events;
    }

    public function audit(): AuditLogInterface
    {
        return new PdoAuditLog($this->db);
    }

    public function authorization(): AuthorizationService
    {
        return $this->authorization ??= new AuthorizationService(new PdoPermissionRepository($this->db));
    }

    public function auth(): AuthService
    {
        return new AuthService(new PdoUserRepository($this->db), new PasswordHasher(), $this->audit());
    }

    public function payments(): PaymentService
    {
        return new PaymentService(new PdoFeeLedgerRepository($this->db), $this->authorization(), $this->db, $this->events, $this->audit(), $this->license());
    }

    /** Clés publiques de confiance (core/Licensing/trusted_keys.php), remplaçables pour les tests. */
    public function withTrustedKeys(array $keys): self
    {
        $this->trustedKeys = array_values($keys);
        $this->license = null;
        return $this;
    }

    public function license(): LicenseManager
    {
        return $this->license ??= new LicenseManager(
            new PdoLicenseStore($this->db),
            new LicenseVerifier($this->trustedKeys ?? (require dirname(__DIR__) . '/Licensing/trusted_keys.php'))
        );
    }

    public function enrollment(): EnrollmentService
    {
        return new EnrollmentService(new PdoEnrollmentStore($this->db), $this->authorization(), $this->license(), $this->db, $this->events, $this->audit());
    }

    /** Dépôt générique d'un modèle : $core->repository(Student::class)->find(3). @param class-string<Entity> $entity */
    public function repository(string $entity): EntityRepository
    {
        return new EntityRepository($this->db, $entity);
    }

    public function reportCards(): ReportCardCalculator
    {
        return new ReportCardCalculator();
    }

    public function bulletinWorkflow(): BulletinWorkflow
    {
        return new BulletinWorkflow();
    }

    public function migrator(string $migrationsDir): Migrator
    {
        return new Migrator($this->db, $migrationsDir);
    }
}
