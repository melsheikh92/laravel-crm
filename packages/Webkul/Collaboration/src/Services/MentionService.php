<?php

namespace Webkul\Collaboration\Services;

use Webkul\Collaboration\Repositories\UserMentionRepository;
use Webkul\Collaboration\Repositories\ChatMessageRepository;
use Webkul\Collaboration\Events\UserMentioned;
use Webkul\User\Repositories\UserRepository;

class MentionService
{
    public function __construct(
        protected UserMentionRepository $mentionRepository,
        protected ChatMessageRepository $messageRepository,
        protected UserRepository $userRepository
    ) {}

    public function parseMentions(string $content, int $messageId, int $channelId): array
    {
        $mentions = [];
        $pattern = '/@(\w+)/';

        preg_match_all($pattern, $content, $matches);

        foreach ($matches[1] as $username) {
            $user = $this->userRepository->findWhere(['name' => $username])->first();
            if ($user) {
                $mention = $this->mentionRepository->create([
                    'message_id' => $messageId,
                    'user_id' => $user->id,
                    'channel_id' => $channelId,
                ]);
                $mentions[] = $mention;

                event(new UserMentioned($mention));
            }
        }

        return $mentions;
    }
}

