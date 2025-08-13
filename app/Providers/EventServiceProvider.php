// ... existing code ...
use Illuminate\Auth\Events\Registered;
use App\Listeners\AssignAdminRole;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
            AssignAdminRole::class, // <-- ASEGÚRATE DE QUE ESTA LÍNEA ESTÉ AQUÍ
        ],
    ];
// ... existing code ...