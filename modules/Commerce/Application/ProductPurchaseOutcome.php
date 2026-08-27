<?php

declare(strict_types=1);

namespace Modules\Commerce\Application;

enum ProductPurchaseOutcome
{
    case Activated;
    case PendingPayment;
    case AlreadyOwned;
    case AlreadyPending;
}
