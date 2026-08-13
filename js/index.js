/*
 * @crmleaf/compliance-calendar - a re-export, not a reimplementation.
 *
 * The arithmetic lives once, in @crmleaf/payroll-js, so a slab change cannot
 * land in one package and miss another. This package exists so a project that
 * only wants Compliance Calendar can install only Compliance Calendar and still get the
 * identical function it would have got from the suite.
 */

export { complianceCalendar, complianceCalendar as calculate, Money } from '@crmleaf/payroll-js';

export { complianceCalendar as default } from '@crmleaf/payroll-js';
