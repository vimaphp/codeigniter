<?php

namespace Vima\CodeIgniter\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Vima\CodeIgniter\Support\Utils;

class VimaDenyUser extends BaseCommand
{
    protected $group = 'Vima';
    protected $name = 'vima:deny';
    protected $description = 'Explicitly deny a permission or role to a user';
    protected $usage = 'vima:deny [user_id] [permission_or_role] [reason] [options]';
    protected $options = [
        '--role' => 'Deny a role instead of a permission',
        '--for'  => 'Duration of denial (e.g. "1 hour", "2 days")',
    ];

    public function run(array $params)
    {
        $user_id = $params[0] ?? null;
        if (empty($user_id)) {
            $user_id = CLI::prompt('User ID', null, 'required');
        }

        $target = $params[1] ?? null;
        if (empty($target)) {
            $target = CLI::prompt('Permission/Role Name', null, 'required');
        }

        $reason = $params[2] ?? null;
        if ($reason === null) {
            $reason = CLI::prompt('Reason (Optional)', '');
        }
        
        $isRole = isset($params['role']) || CLI::getOption('role') !== null;
        $duration = $params['for'] ?? CLI::getOption('for');
        
        $expiresAt = null;
        if ($duration) {
            try {
                $expiresAt = new \DateTime('+' . $duration);
            } catch (\Exception $e) {
                CLI::error("Invalid duration format: {$duration}");
                return;
            }
        }

        if (empty($user_id) || empty($target)) {
            CLI::error('User ID and Target name are required.');
            return;
        }

        $user = Utils::creatVimaUser($user_id);

        try {
            $vima = service('vima');
            if ($isRole) {
                $vima->denyRole($user, $target, $reason, $expiresAt);
                $msg = "Role [{$target}] explicitly denied to user [{$user_id}]";
            } else {
                $vima->deny($user, $target, $reason, $expiresAt);
                $msg = "Permission [{$target}] explicitly denied to user [{$user_id}]";
            }
            
            if ($reason) $msg .= " with reason: {$reason}";
            if ($expiresAt) $msg .= " until " . $expiresAt->format('Y-m-d H:i:s');
            
            CLI::write($msg, 'green');
        } catch (\Exception $e) {
            CLI::error($e->getMessage());
        }
    }
}
