namespace App\Y<?= $year ?>\D<?= $day ?>\P<?= $part ?>;

use App\Challenge as BaseChallenge;

class Challenge extends BaseChallenge
{
    public function resolve(): string
    {
        return 0;
    }
}
