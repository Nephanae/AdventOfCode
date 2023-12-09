namespace App\Y<?= $year ?>\D<?= $day ?>\P<?= $part ?>;

use App\ChallengeAbstract;

class Challenge extends ChallengeAbstract
{
    public function resolve(): string
    {
        return 0;
    }
}
