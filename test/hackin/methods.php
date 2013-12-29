<?php


class NodeVisitor
{
    protected $visitation_methods = [];
    protected $actions = [];

    public function __construct(array $actions=[])
    {
        $this->actions = $this->getActions($actions);
        $this->visitation_methods = $this->getVisitationMethods();
    }

    public function visit($node)
    {
        try {

            // filter dropped (null) nodes before visiting
            $children = array_map([$this, 'visit'], array_filter($node->children));

            if (isset($this->actions[$node->expr_name])) {
                return call_user_func($this->actions[$node->expr_name], $node, $children);
            }

            $method = isset($this->visitation_methods[$node->expr_name])
                ? $this->visitation_methods[$node->expr_name]
                : 'generic_visit';

            return $this->$method($node, $children);

        } catch (VisitationException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new VisitationException($node, $e);
        }
    }

    private function getVisitationMethods()
    {
        $refclass = new ReflectionClass($this);
        $methods = [];
        foreach ($refclass->getMethods() as $refmethod) {
            $name = $refmethod->name;
            if (0 === strpos($name, 'visit_')) {
                $methods[substr($name, 6)] = $name;
            }
        }
        return $methods;
    }

    private function getActions(array $actions)
    {
        foreach ($actions as $expr_name => $action) {
            if ($action instanceof \Closure) {
                $actions[$expr_name] = $action->bindTo($this, $this);
            } else if (method_exists($this, $action)) {
                $actions[$expr_name] = [$this, $action];
            } else {
                unset($actions[$expr_name]);
            }
        }
        return $actions;
    }
}
