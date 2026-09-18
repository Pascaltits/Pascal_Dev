<?php
/**
 * @package     Dotclear
 *
 * @copyright   Pascal Tiberghien
 * @copyright   AGPL-3.0
 */
declare(strict_types=1);

namespace Dotclear\Plugin\quickLinks;

use Dotclear\App;
use Dotclear\Database\Cursor;
use Dotclear\Database\MetaRecord;
use Dotclear\Database\Statement\DeleteStatement;
use Dotclear\Database\Statement\SelectStatement;
use Dotclear\Database\Statement\UpdateStatement;
use Dotclear\Interface\Core\BlogInterface;
use Exception;

/**
 * @brief   The module quickLinks handler.
 * @ingroup quickLinks
 */
class QuickLinks
{
    /**
     * Table name (without prefix).
     */
    public const TABLE_NAME = 'quicklink';

    public const STATUS_OFFLINE = 0;
    public const STATUS_ONLINE  = 1;

    private readonly string $table;

    public function __construct(
        private readonly BlogInterface $blog
    ) {
        $this->table = App::db()->con()->prefix() . self::TABLE_NAME;
    }

    /**
     * Get quick links list.
     *
     * @param   array<string, mixed>   $params
     */
    public function getLinks(array $params = []): MetaRecord
    {
        $sql = new SelectStatement();
        $sql
            ->columns([
                'qlink_id',
                'qlink_label',
                'qlink_url',
                'qlink_position',
                'qlink_status',
            ])
            ->from($this->table)
            ->where('blog_id = ' . $sql->quote($this->blog->id()))
            ->order('qlink_position ASC');

        if (isset($params['qlink_id']) && is_numeric($params['qlink_id'])) {
            $sql->and('qlink_id = ' . (int) $params['qlink_id']);
        }

        if (isset($params['qlink_status']) && is_numeric($params['qlink_status'])) {
            $sql->and('qlink_status = ' . (int) $params['qlink_status']);
        }

        $rs = $sql->select();

        return $rs instanceof MetaRecord ? $rs : MetaRecord::newFromArray([]);
    }

    public function getLink(string $id): MetaRecord
    {
        return $this->getLinks(['qlink_id' => $id]);
    }

    /**
     * Add a quick link.
     *
     * @throws  Exception
     *
     * @return  int     The new link ID
     */
    public function addLink(string $label, string $url, int $status = self::STATUS_ONLINE): int
    {
        $cur = App::db()->con()->openCursor($this->table);

        $cur->blog_id        = $this->blog->id();
        $cur->qlink_label    = $label;
        $cur->qlink_url      = $url;
        $cur->qlink_status   = $status;

        if ($cur->qlink_label === '') {
            throw new Exception(__('You must provide a label.'));
        }

        if ($cur->qlink_url === '') {
            throw new Exception(__('You must provide a URL.'));
        }

        $sql = new SelectStatement();
        $run = $sql
            ->column($sql->max('qlink_id'))
            ->from($this->table)
            ->select();
        $max = $run instanceof MetaRecord ? $run->cardinal() : 0;

        $sql2 = new SelectStatement();
        $run2 = $sql2
            ->column($sql2->max('qlink_position'))
            ->from($this->table)
            ->where('blog_id = ' . $sql2->quote($this->blog->id()))
            ->select();
        $max_pos = $run2 instanceof MetaRecord ? $run2->cardinal() : 0;

        $cur->qlink_id       = $max + 1;
        $cur->qlink_position = $max_pos + 1;

        $cur->insert();

        $this->blog->triggerBlog();

        return $cur->qlink_id;
    }

    /**
     * Update a quick link.
     *
     * @throws  Exception
     */
    public function updateLink(string $id, string $label, string $url, int $status = self::STATUS_ONLINE): void
    {
        $cur = App::db()->con()->openCursor($this->table);

        $cur->qlink_label  = $label;
        $cur->qlink_url    = $url;
        $cur->qlink_status = $status;

        if ($cur->qlink_label === '') {
            throw new Exception(__('You must provide a label.'));
        }

        if ($cur->qlink_url === '') {
            throw new Exception(__('You must provide a URL.'));
        }

        $sql = new UpdateStatement();
        $sql
            ->where('blog_id = ' . $sql->quote($this->blog->id()))
            ->and('qlink_id = ' . (int) $id)
            ->update($cur);

        $this->blog->triggerBlog();
    }

    public function updatePosition(string $id, int $position): void
    {
        $cur                 = App::db()->con()->openCursor($this->table);
        $cur->qlink_position = $position;

        $sql = new UpdateStatement();
        $sql
            ->where('blog_id = ' . $sql->quote($this->blog->id()))
            ->and('qlink_id = ' . (int) $id)
            ->update($cur);

        $this->blog->triggerBlog();
    }

    public function delLink(string $id): void
    {
        $sql = new DeleteStatement();
        $sql
            ->from($this->table)
            ->where('blog_id = ' . $sql->quote($this->blog->id()))
            ->and('qlink_id = ' . (int) $id)
            ->delete();

        $this->blog->triggerBlog();
    }
}
