<?php

declare(strict_types=1);

namespace App\Rules;

use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Username is a class that validates the username where it checks if the username is reserved and if it is already taken.
 *
 * @class Username
 */
final readonly class Username implements ValidationRule
{
    /*
     * Reserved usernames.
     */
    private const RESERVED = [
        '0', 'about', 'access', 'account', 'accounts', 'activate', 'activities', 'activity',
        'ad', 'add', 'address', 'adm', 'admin', 'administrator', 'ads', 'adult', 'affiliate',
        'ajax', 'all', 'alpha', 'analytics', 'api', 'app', 'apps', 'archive', 'article',
        'auth', 'authentication', 'avatar', 'backup', 'banner', 'beta', 'billing', 'blog',
        'bot', 'bots', 'business', 'cache', 'calendar', 'campaign', 'captcha', 'career',
        'careers', 'cart', 'category', 'checkout', 'client', 'code', 'comment', 'comments',
        'community', 'config', 'contact', 'contest', 'create', 'css', 'dashboard', 'data',
        'db', 'default', 'delete', 'demo', 'design', 'developer', 'developers', 'dev', 'docs',
        'domain', 'download', 'edit', 'editor', 'email', 'error', 'event', 'explore', 'faq',
        'favorite', 'feedback', 'file', 'files', 'follow', 'followers', 'forgot', 'forum',
        'forums', 'friend', 'friends', 'ftp', 'game', 'games', 'get', 'ghost', 'github',
        'graph', 'group', 'groups', 'guest', 'help', 'home', 'homepage', 'host', 'hosting',
        'howto', 'html', 'http', 'https', 'icon', 'id', 'image', 'images', 'index', 'info',
        'instagram', 'intranet', 'invite', 'ipad', 'iphone', 'irc', 'issue', 'issues', 'it',
        'item', 'java', 'javascript', 'job', 'jobs', 'join', 'json', 'language', 'last',
        'legal', 'license', 'link', 'linux', 'list', 'lists', 'log', 'login', 'logout', 'logs',
        'mail', 'mailer', 'map', 'marketing', 'master', 'media', 'member', 'members',
        'message', 'messages', 'messenger', 'mobile', 'movie', 'movies', 'music', 'mysql',
        'name', 'network', 'new', 'news', 'newsletter', 'nick', 'nickname', 'note', 'notes',
        'notification', 'notify', 'oauth', 'offer', 'offers', 'official', 'online', 'operator',
        'order', 'orders', 'overview', 'owner', 'page', 'pages', 'panel', 'password', 'payment',
        'photo', 'photos', 'php', 'ping', 'plan', 'plugin', 'plugins', 'policy', 'popular',
        'portal', 'post', 'posts', 'premium', 'press', 'pricing', 'privacy', 'product',
        'products', 'profile', 'project', 'projects', 'public', 'query', 'random', 'ranking',
        'read', 'recent', 'register', 'release', 'remove', 'report', 'reports', 'repository',
        'request', 'reset', 'root', 'rss', 'sale', 'sales', 'save', 'script', 'scripts',
        'search', 'secure', 'security', 'send', 'server', 'service', 'services', 'session',
        'settings', 'setup', 'share', 'shop', 'signin', 'signup', 'site', 'sitemap', 'smartphone',
        'source', 'sql', 'ssl', 'staff', 'stage', 'start', 'static', 'stats', 'status',
        'store', 'subscribe', 'support', 'system', 'tag', 'task', 'team', 'teams', 'tech',
        'terms', 'test', 'tests', 'theme', 'thread', 'threads', 'tools', 'top', 'topic',
        'topics', 'tos', 'tour', 'trends', 'tutorial', 'twitter', 'undefined', 'update',
        'upgrade', 'upload', 'user', 'username', 'users', 'validate', 'video', 'view',
        'web', 'webmail', 'webmaster', 'website', 'welcome', 'widget', 'wiki', 'windows',
        'wordpress', 'workshop', 'world', 'www', 'xml', 'xmp', 'yahoo', 'you', 'your',
        'youtube', 'zero', 'prosop',
    ];

    /**
     * @param User|null $user
     */
    public function __construct(private ?User $user = null) {}

    /**
     * Run the validation rule.
     *
     * @param string $attribute
     * @param mixed $value
     * @param Closure $fail
     *
     * @return void
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Check if the username is reserved.
        if (in_array(mb_strtolower($value), self::RESERVED, true)) {
            $fail('validation.username_reserved');

            return;
        }

        // Check if the username is already taken.
        $query = User::whereRaw('lower(username) = ?', [mb_strtolower($value)]);

        if ($this->user instanceof User) {
            $query->where('id', '!=', $this->user->id);
        }

        if ($query->exists()) {
            $fail('The :attribute has already been taken.');
        }
    }
}
