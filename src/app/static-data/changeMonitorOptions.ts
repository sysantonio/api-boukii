export interface ChangeMonitorOption {
  description: string;
  value: string;
  icon: string;
  class?: string;
}

export const changeMonitorOptions: ChangeMonitorOption[] = [
  {
    description: "select_monitor_free",
    value: "free",
    icon: "done",
    class: "text-green",
  },
  {
    description: "select_monitor_posible",
    value: "posible",
    icon: "warning_amber",
    class: "text-yellow",
  },
  {
    description: "select_monitor_forbidden",
    value: "forbidden",
    icon: "error_outline",
    class: "text-red",
  },
];
